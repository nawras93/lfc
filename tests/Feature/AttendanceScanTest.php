<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\AppKey;
use App\Enums\FixtureStatus;
use App\Models\AttendanceScan;
use App\Models\Candidate;
use App\Models\Fixture;
use App\Models\ParentAccount;
use App\Models\PointRule;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Services\ScanTokenService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceScanTest extends TestCase
{
    use RefreshDatabase;

    private Team $team;

    private Season $season;

    private Fixture $openFixture;

    private Candidate $player;

    private ParentAccount $parent;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->team = Team::query()->firstOrFail();
        $this->season = Season::query()->firstOrFail();

        $this->openFixture = Fixture::query()->create([
            'team_id' => $this->team->id,
            'season_id' => $this->season->id,
            'opponent' => 'Al Sadd SC',
            'venue' => 'Lusail Stadium',
            'kickoff_at' => now()->addDays(7),
            'scan_opens_at' => now()->subHour(),
            'scan_closes_at' => now()->addHours(3),
            'status' => FixtureStatus::OpenForScanning,
        ]);

        $this->player = Candidate::factory()->create([
            'full_name' => 'Test Player',
            'season_id' => $this->season->id,
            'team_id' => $this->team->id,
            'is_player' => true,
        ]);

        $this->parent = ParentAccount::factory()->create();
        $this->parent->players()->attach($this->player);

        $this->admin = User::query()->where('email', env('LFC_ADMIN_EMAIL', 'admin@lfc.test'))->firstOrFail();
    }

    private function parentToken(): string
    {
        return $this->parent->createToken('mobile')->plainTextToken;
    }

    private function adminToken(): string
    {
        return $this->admin->createToken('scanner')->plainTextToken;
    }

    private function issueParentToken(): array
    {
        return app(ScanTokenService::class)->issue($this->parent);
    }

    public function test_token_issue_and_verify_survive_a_string_ttl(): void
    {
        // env() hands back a string whenever SCAN_TOKEN_TTL is set in .env, and
        // Carbon 3's addSeconds() only accepts int|float — so an uncast value
        // 500s every /scan-token request.
        config(['scan.token_ttl' => '900']);

        $service = app(ScanTokenService::class);
        $result = $service->issue($this->parent);

        $this->assertNotEmpty($result['expires_at']);
        $this->assertSame($this->parent->id, $service->verify($result['token']));
    }

    public function test_token_issue_and_verify_round_trip(): void
    {
        $service = app(ScanTokenService::class);
        $result = $service->issue($this->parent);

        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('expires_at', $result);
        $this->assertNotNull($result['token']);
        $this->assertNotNull($result['expires_at']);

        $parentId = $service->verify($result['token']);
        $this->assertSame($this->parent->id, $parentId);
    }

    public function test_expired_token_is_rejected(): void
    {
        $service = app(ScanTokenService::class);
        $past = Carbon::now()->subSeconds(120);

        $result = $service->issue($this->parent, $past);

        $this->assertNull($service->verify($result['token']));
    }

    public function test_tampered_signature_is_rejected(): void
    {
        $service = app(ScanTokenService::class);
        $result = $service->issue($this->parent);

        $tampered = $result['token'].'bad';

        $this->assertNull($service->verify($tampered));
    }

    public function test_wrong_secret_fails_verification(): void
    {
        $service = app(ScanTokenService::class);
        $result = $service->issue($this->parent);

        config(['scan.qr_secret' => 'different-secret']);

        $this->assertNull($service->verify($result['token']));
    }

    public function test_scan_credits_linked_player_on_fixtures_team(): void
    {
        $tokenData = $this->issueParentToken();

        $response = $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $this->openFixture->id,
                'token' => $tokenData['token'],
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'scan_id',
                'credited' => [
                    ['player_id', 'player_name', 'points'],
                ],
                'total_points',
            ]);

        $this->assertSame($this->player->id, $response->json('credited.0.player_id'));
        $this->assertGreaterThan(0, $response->json('total_points'));

        $this->assertDatabaseHas('attendance_scans', [
            'parent_account_id' => $this->parent->id,
            'fixture_id' => $this->openFixture->id,
            'scanned_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('point_transactions', [
            'candidate_id' => $this->player->id,
            'source_type' => (new AttendanceScan)->getMorphClass(),
        ]);
    }

    public function test_linked_player_on_different_team_not_credited(): void
    {
        $otherTeam = Team::query()->create([
            'name' => 'LFC U14',
            'age_group' => 'U14',
            'season_id' => $this->season->id,
        ]);

        $otherPlayer = Candidate::factory()->create([
            'full_name' => 'Other Team Player',
            'season_id' => $this->season->id,
            'team_id' => $otherTeam->id,
            'is_player' => true,
        ]);

        $this->parent->players()->attach($otherPlayer);

        $tokenData = $this->issueParentToken();

        $response = $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $this->openFixture->id,
                'token' => $tokenData['token'],
            ]);

        $response->assertOk();

        $creditedIds = collect($response->json('credited'))->pluck('player_id')->toArray();

        $this->assertContains($this->player->id, $creditedIds);
        $this->assertNotContains($otherPlayer->id, $creditedIds);
    }

    public function test_one_scan_dedupe_second_scan_returns_409(): void
    {
        $tokenData = $this->issueParentToken();

        $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $this->openFixture->id,
                'token' => $tokenData['token'],
            ])->assertOk();

        $secondToken = app(ScanTokenService::class)->issue($this->parent);

        $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $this->openFixture->id,
                'token' => $secondToken['token'],
            ])->assertStatus(409)
            ->assertJson(['message' => 'Already scanned for this match.']);

        $this->assertSame(
            1,
            AttendanceScan::query()
                ->where('parent_account_id', $this->parent->id)
                ->where('fixture_id', $this->openFixture->id)
                ->count(),
        );
    }

    public function test_scan_rejected_when_fixture_not_open_for_scanning(): void
    {
        $closedFixture = Fixture::query()->create([
            'team_id' => $this->team->id,
            'season_id' => $this->season->id,
            'opponent' => 'Closed FC',
            'venue' => 'Test',
            'kickoff_at' => now()->addDays(7),
            'status' => FixtureStatus::Scheduled,
        ]);

        $tokenData = $this->issueParentToken();

        $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $closedFixture->id,
                'token' => $tokenData['token'],
            ])->assertStatus(422)
            ->assertJson(['message' => 'Match is not open for scanning.']);
    }

    public function test_scan_rejected_when_no_linked_player_on_team(): void
    {
        $unlinkedParent = ParentAccount::factory()->create();
        $unlinkedParentToken = app(ScanTokenService::class)->issue($unlinkedParent);

        $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $this->openFixture->id,
                'token' => $unlinkedParentToken['token'],
            ])->assertStatus(422)
            ->assertJson(['message' => 'No linked player on this match\'s team.']);
    }

    public function test_scan_rejected_with_invalid_token(): void
    {
        $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $this->openFixture->id,
                'token' => 'invalid-token-string',
            ])->assertStatus(422)
            ->assertJson(['message' => 'Invalid or expired QR.']);
    }

    public function test_scan_rejected_with_expired_token(): void
    {
        $service = app(ScanTokenService::class);
        $past = Carbon::now()->subSeconds(120);
        $result = $service->issue($this->parent, $past);

        $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $this->openFixture->id,
                'token' => $result['token'],
            ])->assertStatus(422)
            ->assertJson(['message' => 'Invalid or expired QR.']);
    }

    public function test_parent_token_rejected_on_scan_endpoint(): void
    {
        $this->withToken($this->parentToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $this->openFixture->id,
                'token' => 'some-token',
            ])->assertStatus(403);
    }

    public function test_staff_token_rejected_on_scan_token_endpoint(): void
    {
        $this->withToken($this->adminToken())
            ->getJson('/api/v1/scan-token')
            ->assertStatus(403);
    }

    public function test_parent_can_request_scan_token(): void
    {
        $this->withToken($this->parentToken())
            ->getJson('/api/v1/scan-token')
            ->assertOk()
            ->assertJsonStructure(['token', 'expires_at']);
    }

    public function test_scan_token_requires_authentication(): void
    {
        $this->getJson('/api/v1/scan-token')->assertUnauthorized();
    }

    public function test_scan_requires_authentication(): void
    {
        $this->postJson('/api/v1/scan', [
            'fixture_id' => 1,
            'token' => 'test',
        ])->assertUnauthorized();
    }

    public function test_staff_fixtures_endpoint_returns_open_fixtures_only(): void
    {
        $closedFixture = Fixture::query()->create([
            'team_id' => $this->team->id,
            'season_id' => $this->season->id,
            'opponent' => 'Closed Fixture',
            'venue' => 'Training Ground',
            'kickoff_at' => now()->addDays(2),
            'scan_opens_at' => now()->addDay(),
            'scan_closes_at' => now()->addDays(2),
            'status' => FixtureStatus::OpenForScanning,
        ]);

        $this->withToken($this->adminToken())
            ->getJson('/api/v1/staff/fixtures')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $this->openFixture->id,
                'team_name' => $this->team->name,
            ])
            ->assertJsonMissing([
                'id' => $closedFixture->id,
                'opponent' => 'Closed Fixture',
            ]);
    }

    public function test_staff_fixtures_endpoint_without_header_defaults_to_app_one(): void
    {
        $appTwoFixture = Fixture::query()->create([
            'team_id' => null,
            'season_id' => $this->season->id,
            'opponent' => 'App Two Fixture',
            'venue' => 'Training Ground',
            'kickoff_at' => now()->addDays(2),
            'scan_opens_at' => now()->subHour(),
            'scan_closes_at' => now()->addHours(2),
            'status' => FixtureStatus::OpenForScanning,
            'app' => AppKey::AppTwo,
        ]);

        $this->withToken($this->adminToken())
            ->getJson('/api/v1/staff/fixtures')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $this->openFixture->id,
                'team_name' => $this->team->name,
            ])
            ->assertJsonMissing([
                'id' => $appTwoFixture->id,
                'opponent' => 'App Two Fixture',
            ]);
    }

    public function test_staff_fixtures_endpoint_with_app_two_header_returns_only_app_two_open_fixtures(): void
    {
        $appTwoFixture = Fixture::query()->create([
            'team_id' => null,
            'season_id' => $this->season->id,
            'opponent' => 'App Two Fixture',
            'venue' => 'Training Ground',
            'kickoff_at' => now()->addDays(2),
            'scan_opens_at' => now()->subHour(),
            'scan_closes_at' => now()->addHours(2),
            'status' => FixtureStatus::OpenForScanning,
            'app' => AppKey::AppTwo,
        ]);

        $this->withToken($this->adminToken())
            ->withHeader('X-App-Key', AppKey::AppTwo->value)
            ->getJson('/api/v1/staff/fixtures')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $appTwoFixture->id,
                'opponent' => 'App Two Fixture',
            ])
            ->assertJsonMissing([
                'id' => $this->openFixture->id,
                'team_name' => $this->team->name,
            ]);
    }

    public function test_staff_fixtures_endpoint_with_app_one_header_returns_only_app_one_open_fixtures(): void
    {
        $appTwoFixture = Fixture::query()->create([
            'team_id' => null,
            'season_id' => $this->season->id,
            'opponent' => 'App Two Fixture',
            'venue' => 'Training Ground',
            'kickoff_at' => now()->addDays(2),
            'scan_opens_at' => now()->subHour(),
            'scan_closes_at' => now()->addHours(2),
            'status' => FixtureStatus::OpenForScanning,
            'app' => AppKey::AppTwo,
        ]);

        $this->withToken($this->adminToken())
            ->withHeader('X-App-Key', AppKey::AppOne->value)
            ->getJson('/api/v1/staff/fixtures')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $this->openFixture->id,
                'team_name' => $this->team->name,
            ])
            ->assertJsonMissing([
                'id' => $appTwoFixture->id,
                'opponent' => 'App Two Fixture',
            ]);
    }

    public function test_parent_token_rejected_on_staff_fixtures_endpoint(): void
    {
        $this->withToken($this->parentToken())
            ->getJson('/api/v1/staff/fixtures')
            ->assertStatus(403);
    }

    public function test_scan_rejects_parent_and_fixture_from_different_apps(): void
    {
        $member = ParentAccount::factory()->create([
            'account_type' => AccountType::Member,
            'app' => AppKey::AppTwo,
        ]);
        $memberToken = app(ScanTokenService::class)->issue($member);

        $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $this->openFixture->id,
                'token' => $memberToken['token'],
            ])->assertStatus(422)
            ->assertJson(['message' => 'This QR is not valid for this match.']);

        $appTwoFixture = Fixture::query()->create([
            'team_id' => null,
            'season_id' => $this->season->id,
            'opponent' => 'App Two Fixture',
            'venue' => 'Training Ground',
            'kickoff_at' => now()->addDays(2),
            'scan_opens_at' => now()->subHour(),
            'scan_closes_at' => now()->addHours(2),
            'status' => FixtureStatus::OpenForScanning,
            'app' => AppKey::AppTwo,
        ]);

        $parentToken = $this->issueParentToken();

        $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $appTwoFixture->id,
                'token' => $parentToken['token'],
            ])->assertStatus(422)
            ->assertJson(['message' => 'This QR is not valid for this match.']);

        $this->assertDatabaseMissing('attendance_scans', [
            'parent_account_id' => $member->id,
            'fixture_id' => $this->openFixture->id,
        ]);
        $this->assertDatabaseMissing('attendance_scans', [
            'parent_account_id' => $this->parent->id,
            'fixture_id' => $appTwoFixture->id,
        ]);
    }

    public function test_non_scanner_role_cannot_login(): void
    {
        $plainUser = User::factory()->create([
            'email' => 'plain@example.com',
            'password' => 'secret123',
        ]);

        $this->postJson('/api/v1/staff/login', [
            'email' => 'plain@example.com',
            'password' => 'secret123',
        ])->assertStatus(403);
    }

    public function test_staff_login_with_scanner_role_returns_token(): void
    {
        $this->postJson('/api/v1/staff/login', [
            'email' => env('LFC_ADMIN_EMAIL', 'admin@lfc.test'),
            'password' => env('LFC_ADMIN_PASSWORD', 'password'),
        ])->assertOk()
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email']]);
    }

    public function test_staff_login_with_wrong_password_is_rejected(): void
    {
        $this->postJson('/api/v1/staff/login', [
            'email' => env('LFC_ADMIN_EMAIL', 'admin@lfc.test'),
            'password' => 'wrong-password',
        ])->assertUnprocessable();
    }

    public function test_credited_points_equal_active_rule_value(): void
    {
        $tokenData = $this->issueParentToken();

        $response = $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $this->openFixture->id,
                'token' => $tokenData['token'],
            ]);

        $response->assertOk();

        $expectedPoints = $response->json('credited.0.points');

        $this->assertGreaterThan(0, $expectedPoints);

        $this->assertDatabaseHas('point_transactions', [
            'candidate_id' => $this->player->id,
            'points' => $expectedPoints,
        ]);
    }

    public function test_scan_row_records_correct_data(): void
    {
        $tokenData = $this->issueParentToken();

        $now = Carbon::now();

        $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $this->openFixture->id,
                'token' => $tokenData['token'],
            ])->assertOk();

        $scan = AttendanceScan::query()
            ->where('parent_account_id', $this->parent->id)
            ->where('fixture_id', $this->openFixture->id)
            ->first();

        $this->assertNotNull($scan);
        $this->assertSame($this->admin->id, $scan->scanned_by);
        $this->assertNotNull($scan->scanned_at);
    }

    public function test_scan_credits_zero_when_no_rule_but_still_records_attendance(): void
    {
        // Deactivate all rules so no rule matches
        PointRule::query()->update(['is_active' => false]);

        $tokenData = $this->issueParentToken();

        $response = $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $this->openFixture->id,
                'token' => $tokenData['token'],
            ]);

        $response->assertOk();

        $this->assertSame(0, $response->json('total_points'));
        $this->assertSame(0, $response->json('credited.0.points'));

        $this->assertDatabaseHas('attendance_scans', [
            'parent_account_id' => $this->parent->id,
            'fixture_id' => $this->openFixture->id,
        ]);
    }

    public function test_scan_records_use_morph_source(): void
    {
        $tokenData = $this->issueParentToken();

        $this->withToken($this->adminToken())
            ->postJson('/api/v1/scan', [
                'fixture_id' => $this->openFixture->id,
                'token' => $tokenData['token'],
            ])->assertOk();

        $scan = AttendanceScan::query()
            ->where('parent_account_id', $this->parent->id)
            ->where('fixture_id', $this->openFixture->id)
            ->firstOrFail();

        $this->assertCount(1, $scan->transactions);

        $txn = $scan->transactions->first();
        $this->assertSame($this->player->id, $txn->candidate_id);
    }
}
