<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\FixtureStatus;
use App\Enums\PointTransactionType;
use App\Enums\RedemptionStatus;
use App\Enums\RedemptionType;
use App\Models\AttendanceScan;
use App\Models\Candidate;
use App\Models\Fixture;
use App\Models\ParentAccount;
use App\Models\PointTransaction;
use App\Models\Redemption;
use App\Models\RedemptionItem;
use App\Models\Season;
use App\Models\Team;
use App\Services\LoyaltyMetrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoyaltyMetricsTest extends TestCase
{
    use RefreshDatabase;

    private LoyaltyMetrics $metrics;
    private Candidate $player;
    private ParentAccount $vvipAccount;
    private ParentAccount $parent;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->metrics = app(LoyaltyMetrics::class);

        $season = Season::query()->firstOrFail();

        $this->player = Candidate::factory()->create([
            'season_id' => $season->id,
            'is_player' => true,
        ]);

        $this->parent = ParentAccount::factory()->create([
            'is_vvip' => false,
            'account_type' => AccountType::Parent,
        ]);

        $this->vvipAccount = ParentAccount::factory()->create([
            'is_vvip' => true,
            'account_type' => AccountType::VvipClient,
        ]);
    }

    public function test_points_issued_counts_both_owners(): void
    {
        $before = $this->metrics->pointsIssued();

        PointTransaction::create([
            'candidate_id' => $this->player->id,
            'type' => PointTransactionType::Earn,
            'points' => 100,
        ]);
        PointTransaction::create([
            'parent_account_id' => $this->vvipAccount->id,
            'type' => PointTransactionType::Adjust,
            'points' => 250,
        ]);

        $this->assertSame($before + 350, $this->metrics->pointsIssued());
    }

    public function test_points_redeemed_counts_both_owners(): void
    {
        $before = $this->metrics->pointsRedeemed();

        PointTransaction::create([
            'candidate_id' => $this->player->id,
            'type' => PointTransactionType::Redeem,
            'points' => -30,
        ]);
        PointTransaction::create([
            'parent_account_id' => $this->vvipAccount->id,
            'type' => PointTransactionType::Redeem,
            'points' => -70,
        ]);

        $this->assertSame($before + 100, $this->metrics->pointsRedeemed());
    }

    public function test_outstanding_liability_includes_account_owned_points(): void
    {
        $before = $this->metrics->outstandingLiability();

        PointTransaction::create([
            'candidate_id' => $this->player->id,
            'type' => PointTransactionType::Earn,
            'points' => 100,
        ]);
        PointTransaction::create([
            'candidate_id' => $this->player->id,
            'type' => PointTransactionType::Redeem,
            'points' => -30,
        ]);
        PointTransaction::create([
            'parent_account_id' => $this->vvipAccount->id,
            'type' => PointTransactionType::Adjust,
            'points' => 200,
        ]);
        PointTransaction::create([
            'parent_account_id' => $this->vvipAccount->id,
            'type' => PointTransactionType::Redeem,
            'points' => -50,
        ]);

        $expectedDelta = 100 - 30 + 200 - 50;
        $this->assertSame($before + $expectedDelta, $this->metrics->outstandingLiability());
    }

    public function test_outstanding_liability_equals_full_ledger_sum(): void
    {
        $ledgerSum = (int) DB::table('point_transactions')->sum('points');

        $this->assertSame($ledgerSum, $this->metrics->outstandingLiability());
    }

    public function test_pending_fulfillments_counts_only_issued(): void
    {
        $before = $this->metrics->pendingFulfillments();

        $item = RedemptionItem::query()->create([
            'name' => 'Test Redemption Item',
            'type' => RedemptionType::Merch,
            'points_cost' => 10,
            'stock' => null,
            'is_active' => true,
        ]);

        Redemption::create([
            'parent_account_id' => $this->parent->id,
            'candidate_id' => $this->player->id,
            'redemption_item_id' => $item->id,
            'points_spent' => 10,
            'voucher_code' => 'METRICSTEST1',
            'status' => RedemptionStatus::Issued,
        ]);
        Redemption::create([
            'parent_account_id' => $this->parent->id,
            'candidate_id' => $this->player->id,
            'redemption_item_id' => $item->id,
            'points_spent' => 10,
            'voucher_code' => 'METRICSTEST2',
            'status' => RedemptionStatus::Fulfilled,
        ]);
        Redemption::create([
            'parent_account_id' => $this->parent->id,
            'candidate_id' => $this->player->id,
            'redemption_item_id' => $item->id,
            'points_spent' => 10,
            'voucher_code' => 'METRICSTEST3',
            'status' => RedemptionStatus::Cancelled,
        ]);

        $this->assertSame($before + 1, $this->metrics->pendingFulfillments());
    }

    public function test_attendance_scans_count(): void
    {
        $before = $this->metrics->attendanceScans();

        $team = Team::query()->firstOrFail();
        $season = Season::query()->firstOrFail();

        $fixture1 = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent' => 'Test FC 1',
            'venue' => 'Lusail Stadium',
            'kickoff_at' => now()->addDays(7),
            'scan_opens_at' => now()->subHour(),
            'scan_closes_at' => now()->addHours(3),
            'status' => FixtureStatus::OpenForScanning,
        ]);

        $fixture2 = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent' => 'Test FC 2',
            'venue' => 'Lusail Stadium',
            'kickoff_at' => now()->addDays(14),
            'scan_opens_at' => now()->subHour(),
            'scan_closes_at' => now()->addHours(3),
            'status' => FixtureStatus::OpenForScanning,
        ]);

        AttendanceScan::create([
            'parent_account_id' => $this->parent->id,
            'fixture_id' => $fixture1->id,
            'scanned_at' => now(),
        ]);
        AttendanceScan::create([
            'parent_account_id' => $this->parent->id,
            'fixture_id' => $fixture2->id,
            'scanned_at' => now(),
        ]);

        $this->assertSame($before + 2, $this->metrics->attendanceScans());
    }

    public function test_vvip_clients_count(): void
    {
        $before = $this->metrics->vvipClients();

        ParentAccount::factory()->create([
            'is_vvip' => true,
            'account_type' => AccountType::VvipClient,
        ]);
        ParentAccount::factory()->create([
            'is_vvip' => true,
            'account_type' => AccountType::VvipClient,
        ]);

        $this->assertSame($before + 2, $this->metrics->vvipClients());
    }

    public function test_non_vvip_not_counted(): void
    {
        $before = $this->metrics->vvipClients();

        ParentAccount::factory()->create([
            'is_vvip' => false,
            'account_type' => AccountType::Parent,
        ]);

        $this->assertSame($before, $this->metrics->vvipClients());
    }
}
