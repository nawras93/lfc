<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\AppKey;
use App\Enums\FixtureStatus;
use App\Enums\LedgerUnit;
use App\Enums\PointTransactionType;
use App\Filament\AppTwo\Resources\Members\MemberResource;
use App\Models\AttendanceScan;
use App\Models\Candidate;
use App\Models\Fixture;
use App\Models\ParentAccount;
use App\Models\PointTransaction;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Services\ScanTokenService;
use App\Support\AppContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppTwoMemberTest extends TestCase
{
    use RefreshDatabase;

    private Team $team;

    private Season $season;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->team = Team::query()->firstOrFail();
        $this->season = Season::query()->firstOrFail();
        $this->admin = User::query()->where('email', env('LFC_ADMIN_EMAIL', 'admin@lfc.test'))->firstOrFail();
    }

    protected function tearDown(): void
    {
        app(AppContext::class)->clear();

        parent::tearDown();
    }

    public function test_register_creates_an_app_two_member_and_returns_a_usable_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Supporter One',
            'email' => 'supporter@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'phone' => '+97455550000',
        ]);

        $response->assertOk()
            ->assertJsonPath('parent.app', AppKey::AppTwo->value)
            ->assertJsonPath('parent.account_type', AccountType::Member->value)
            ->assertJsonPath('parent.discount_percent', 0);

        $member = ParentAccount::withoutAppScope()->where('email', 'supporter@example.com')->firstOrFail();

        $this->assertSame(AppKey::AppTwo, $member->app);
        $this->assertSame(AccountType::Member, $member->account_type);
        $this->assertNotNull($member->accepted_at);

        $this->withToken($response->json('token'))
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $member->id)
            ->assertJsonPath('data.app', AppKey::AppTwo->value);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        ParentAccount::factory()->create([
            'email' => 'duplicate@example.com',
            'app' => AppKey::AppOne,
        ]);

        $this->postJson('/api/v1/auth/register', [
            'name' => 'Duplicate Supporter',
            'email' => 'duplicate@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertUnprocessable();
    }

    public function test_member_scan_accrues_discount_once_and_me_reports_percentage(): void
    {
        $member = $this->createMember();
        $fixture = $this->createOpenFixture();

        $response = $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $fixture->id,
                'token' => $this->issueScanToken($member),
            ]);

        $response->assertOk()
            ->assertJsonPath('discount_added_percent', 0.5)
            ->assertJsonPath('discount_percent', 0.5)
            ->assertJsonPath('discount_cap_percent', 10);

        $this->assertDatabaseHas('attendance_scans', [
            'parent_account_id' => $member->id,
            'fixture_id' => $fixture->id,
            'scanned_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('point_transactions', [
            'parent_account_id' => $member->id,
            'type' => PointTransactionType::Earn->value,
            'points' => 50,
            'unit' => LedgerUnit::DiscountPct->value,
            'source_type' => (new AttendanceScan)->getMorphClass(),
        ]);

        $this->actingAs($member, 'sanctum')
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.account_balance', 0)
            ->assertJsonPath('data.discount_percent', 0.5);
    }

    public function test_member_scan_dedupes_without_second_accrual(): void
    {
        $member = $this->createMember();
        $fixture = $this->createOpenFixture();

        $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $fixture->id,
                'token' => $this->issueScanToken($member),
            ])->assertOk();

        $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $fixture->id,
                'token' => $this->issueScanToken($member),
            ])->assertStatus(409)
            ->assertJson(['message' => 'Already scanned for this match.']);

        $this->assertSame(1, AttendanceScan::query()
            ->where('parent_account_id', $member->id)
            ->where('fixture_id', $fixture->id)
            ->count());
        $this->assertSame(1, PointTransaction::query()
            ->where('parent_account_id', $member->id)
            ->where('unit', LedgerUnit::DiscountPct->value)
            ->count());
    }

    public function test_member_scan_respects_the_discount_cap_and_still_records_attendance(): void
    {
        $member = $this->createMember();
        $fixture = $this->createOpenFixture([
            'opponent' => 'Cap Fixture',
        ]);

        PointTransaction::query()->create([
            'parent_account_id' => $member->id,
            'type' => PointTransactionType::Earn,
            'points' => 1000,
            'unit' => LedgerUnit::DiscountPct,
            'reason' => 'Already capped',
        ]);

        $response = $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $fixture->id,
                'token' => $this->issueScanToken($member),
            ]);

        $response->assertOk()
            ->assertJsonPath('discount_added_percent', 0)
            ->assertJsonPath('discount_percent', 10)
            ->assertJsonPath('discount_cap_percent', 10);

        $this->assertDatabaseHas('attendance_scans', [
            'parent_account_id' => $member->id,
            'fixture_id' => $fixture->id,
        ]);

        $this->assertSame(1, PointTransaction::query()
            ->where('parent_account_id', $member->id)
            ->where('unit', LedgerUnit::DiscountPct->value)
            ->count());
    }

    public function test_app_one_parent_scan_path_still_credits_linked_player(): void
    {
        $parent = ParentAccount::factory()->create([
            'app' => AppKey::AppOne,
            'account_type' => AccountType::Parent,
        ]);

        $player = Candidate::factory()->create([
            'full_name' => 'Academy Player',
            'season_id' => $this->season->id,
            'team_id' => $this->team->id,
            'is_player' => true,
        ]);

        $parent->players()->attach($player);
        $fixture = $this->createOpenFixture();

        $response = $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $fixture->id,
                'token' => $this->issueScanToken($parent),
            ]);

        $response->assertOk()
            ->assertJsonPath('credited.0.player_id', $player->id);

        $this->assertDatabaseHas('point_transactions', [
            'candidate_id' => $player->id,
            'unit' => LedgerUnit::Points->value,
            'source_type' => (new AttendanceScan)->getMorphClass(),
        ]);
    }

    public function test_member_resource_query_returns_only_app_two_members(): void
    {
        ParentAccount::factory()->create([
            'app' => AppKey::AppOne,
            'account_type' => AccountType::Parent,
        ]);

        ParentAccount::factory()->create([
            'app' => AppKey::AppOne,
            'account_type' => AccountType::Member,
        ]);

        $member = ParentAccount::factory()->create([
            'app' => AppKey::AppTwo,
            'account_type' => AccountType::Member,
        ]);

        ParentAccount::factory()->create([
            'app' => AppKey::AppTwo,
            'account_type' => AccountType::VvipClient,
            'is_vvip' => true,
        ]);

        app(AppContext::class)->setCurrent(AppKey::AppTwo);

        $memberIds = MemberResource::getEloquentQuery()->pluck('id')->all();

        $this->assertContains($member->id, $memberIds);
        $this->assertNotEmpty($memberIds);
        $this->assertTrue(MemberResource::getEloquentQuery()->get()->every(
            fn (ParentAccount $record): bool => $record->app === AppKey::AppTwo
                && $record->account_type === AccountType::Member,
        ));
    }

    private function createMember(): ParentAccount
    {
        return ParentAccount::factory()->create([
            'app' => AppKey::AppTwo,
            'account_type' => AccountType::Member,
            'is_vvip' => false,
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createOpenFixture(array $overrides = []): Fixture
    {
        return Fixture::query()->create(array_merge([
            'team_id' => $this->team->id,
            'season_id' => $this->season->id,
            'opponent' => 'Open Fixture',
            'venue' => 'Lusail Stadium',
            'kickoff_at' => now()->addDays(7),
            'scan_opens_at' => now()->subHour(),
            'scan_closes_at' => now()->addHour(),
            'status' => FixtureStatus::OpenForScanning,
            'app' => AppKey::AppOne,
        ], $overrides));
    }

    private function adminToken(): string
    {
        return $this->admin->createToken('scanner')->plainTextToken;
    }

    private function issueScanToken(ParentAccount $account): string
    {
        return app(ScanTokenService::class)->issue($account)['token'];
    }
}
