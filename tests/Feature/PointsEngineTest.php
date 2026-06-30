<?php

namespace Tests\Feature;

use App\Enums\FixtureStatus;
use App\Enums\PointRuleType;
use App\Enums\PointTransactionType;
use App\Models\Candidate;
use App\Models\Fixture;
use App\Models\PointRule;
use App\Models\PointTransaction;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Services\PointsEngine;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PointsEngineTest extends TestCase
{
    use RefreshDatabase;

    private PointsEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->engine = app(PointsEngine::class);
    }

    public function test_balance_is_derived_from_sum_of_transactions(): void
    {
        $player = Candidate::factory()->create(['season_id' => Season::query()->firstOrFail()->id]);

        PointTransaction::query()->create(['candidate_id' => $player->id, 'type' => PointTransactionType::Earn, 'points' => 50]);
        PointTransaction::query()->create(['candidate_id' => $player->id, 'type' => PointTransactionType::Earn, 'points' => 30]);
        PointTransaction::query()->create(['candidate_id' => $player->id, 'type' => PointTransactionType::Redeem, 'points' => -20]);

        $this->assertSame(60, $player->fresh()->pointsBalance());
    }

    public function test_balance_is_zero_when_no_transactions(): void
    {
        $player = Candidate::factory()->create(['season_id' => Season::query()->firstOrFail()->id]);

        $this->assertSame(0, $player->pointsBalance());
    }

    public function test_point_transaction_update_throws(): void
    {
        $player = Candidate::factory()->create(['season_id' => Season::query()->firstOrFail()->id]);
        $txn = PointTransaction::query()->create(['candidate_id' => $player->id, 'type' => PointTransactionType::Earn, 'points' => 10]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('append-only');

        $txn->update(['points' => 99]);
    }

    public function test_point_transaction_delete_throws(): void
    {
        $player = Candidate::factory()->create(['season_id' => Season::query()->firstOrFail()->id]);
        $txn = PointTransaction::query()->create(['candidate_id' => $player->id, 'type' => PointTransactionType::Earn, 'points' => 10]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('append-only');

        $txn->delete();
    }

    public function test_point_transaction_force_delete_throws(): void
    {
        $player = Candidate::factory()->create(['season_id' => Season::query()->firstOrFail()->id]);
        $txn = PointTransaction::query()->create(['candidate_id' => $player->id, 'type' => PointTransactionType::Earn, 'points' => 10]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('append-only');

        $txn->forceDelete();
    }

    public function test_points_value_fixed(): void
    {
        $rule = PointRule::query()->create([
            'name' => 'Fixed test',
            'type' => PointRuleType::Fixed,
            'points' => 25,
            'is_active' => true,
        ]);

        $this->assertSame(25, $rule->pointsValue());
    }

    public function test_points_value_percentage_rounding(): void
    {
        $rule = PointRule::query()->create([
            'name' => 'Percentage test',
            'type' => PointRuleType::Percentage,
            'percentage' => 5.50,
            'base_amount' => 200.00,
            'is_active' => true,
        ]);

        $this->assertSame(11, $rule->pointsValue());
    }

    public function test_points_value_percentage_zero_base(): void
    {
        $rule = PointRule::query()->create([
            'name' => 'Zero base',
            'type' => PointRuleType::Percentage,
            'percentage' => 10.00,
            'base_amount' => 0.00,
            'is_active' => true,
        ]);

        $this->assertSame(0, $rule->pointsValue());
    }

    public function test_resolveRule_returns_active_rule(): void
    {
        $team = Team::query()->firstOrFail();
        $season = Season::query()->firstOrFail();
        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent' => 'Test FC',
            'venue' => 'Test Stadium',
            'kickoff_at' => now()->addDays(7),
            'status' => FixtureStatus::Scheduled,
        ]);

        $rule = $this->engine->resolveRule($fixture);

        $this->assertNotNull($rule);
        $this->assertSame('Bonus attendance — 5% of fee', $rule->name);
    }

    public function test_resolveRule_returns_null_when_no_active_rule(): void
    {
        $team = Team::query()->firstOrFail();
        $season = Season::query()->firstOrFail();
        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent' => 'Test FC',
            'venue' => 'Test Stadium',
            'kickoff_at' => now()->addDays(7),
            'status' => FixtureStatus::Scheduled,
        ]);

        PointRule::query()->update(['is_active' => false]);

        $rule = $this->engine->resolveRule($fixture);

        $this->assertNull($rule);
    }

    public function test_resolveRule_respects_date_window(): void
    {
        $team = Team::query()->firstOrFail();
        $season = Season::query()->firstOrFail();
        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent' => 'Test FC',
            'venue' => 'Test Stadium',
            'kickoff_at' => now()->addDays(7),
            'status' => FixtureStatus::Scheduled,
        ]);

        PointRule::query()->update(['is_active' => false]);

        $windowRule = PointRule::query()->create([
            'name' => 'Windowed rule',
            'type' => PointRuleType::Fixed,
            'points' => 5,
            'is_active' => true,
            'starts_at' => now()->subDays(10),
            'ends_at' => now()->addDays(30),
        ]);

        $rule = $this->engine->resolveRule($fixture);
        $this->assertNotNull($rule);
        $this->assertSame('Windowed rule', $rule->name);

        $expiredRule = $this->engine->resolveRule($fixture, Carbon::now()->subDays(20));
        $this->assertNull($expiredRule);
    }

    public function test_resolveRule_prefers_team_scoped_over_global(): void
    {
        $team = Team::query()->firstOrFail();
        $season = Season::query()->firstOrFail();
        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent' => 'Test FC',
            'venue' => 'Test Stadium',
            'kickoff_at' => now()->addDays(7),
            'status' => FixtureStatus::Scheduled,
        ]);

        PointRule::query()->update(['is_active' => false]);

        $globalRule = PointRule::query()->create([
            'name' => 'Global',
            'type' => PointRuleType::Fixed,
            'points' => 5,
            'is_active' => true,
            'team_id' => null,
            'season_id' => null,
            'priority' => 0,
        ]);

        $teamRule = PointRule::query()->create([
            'name' => 'Team scoped',
            'type' => PointRuleType::Fixed,
            'points' => 20,
            'is_active' => true,
            'team_id' => $team->id,
            'season_id' => null,
            'priority' => 0,
        ]);

        $rule = $this->engine->resolveRule($fixture);
        $this->assertNotNull($rule);
        $this->assertSame('Team scoped', $rule->name);
    }

    public function test_resolveRule_prefers_higher_priority(): void
    {
        $team = Team::query()->firstOrFail();
        $season = Season::query()->firstOrFail();
        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent' => 'Test FC',
            'venue' => 'Test Stadium',
            'kickoff_at' => now()->addDays(7),
            'status' => FixtureStatus::Scheduled,
        ]);

        PointRule::query()->update(['is_active' => false]);

        $lowPrio = PointRule::query()->create([
            'name' => 'Low priority',
            'type' => PointRuleType::Fixed,
            'points' => 1,
            'is_active' => true,
            'team_id' => null,
            'season_id' => null,
            'priority' => 0,
        ]);

        $highPrio = PointRule::query()->create([
            'name' => 'High priority',
            'type' => PointRuleType::Fixed,
            'points' => 99,
            'is_active' => true,
            'team_id' => null,
            'season_id' => null,
            'priority' => 100,
        ]);

        $rule = $this->engine->resolveRule($fixture);
        $this->assertNotNull($rule);
        $this->assertSame('High priority', $rule->name);
    }

    public function test_credit_writes_earn_transaction(): void
    {
        $player = Candidate::factory()->create(['season_id' => Season::query()->firstOrFail()->id]);
        $team = Team::query()->firstOrFail();
        $season = Season::query()->firstOrFail();
        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent' => 'Test FC',
            'venue' => 'Test Stadium',
            'kickoff_at' => now()->addDays(7),
            'status' => FixtureStatus::Scheduled,
        ]);

        $txn = $this->engine->credit($player, $fixture);

        $this->assertNotNull($txn);
        $this->assertSame($player->id, $txn->candidate_id);
        $this->assertSame(PointTransactionType::Earn, $txn->type);
        $this->assertSame(10, $txn->points);
        $this->assertNotNull($txn->point_rule_id);
        $this->assertSame(10, $player->fresh()->pointsBalance());
    }

    public function test_credit_returns_null_when_no_matching_rule(): void
    {
        $player = Candidate::factory()->create(['season_id' => Season::query()->firstOrFail()->id]);
        $team = Team::query()->firstOrFail();
        $season = Season::query()->firstOrFail();
        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent' => 'Test FC',
            'venue' => 'Test Stadium',
            'kickoff_at' => now()->addDays(7),
            'status' => FixtureStatus::Scheduled,
        ]);

        PointRule::query()->update(['is_active' => false]);

        $txn = $this->engine->credit($player, $fixture);

        $this->assertNull($txn);
        $this->assertSame(0, $player->fresh()->pointsBalance());
    }

    public function test_credit_accepts_optional_source(): void
    {
        $player = Candidate::factory()->create(['season_id' => Season::query()->firstOrFail()->id]);
        $team = Team::query()->firstOrFail();
        $season = Season::query()->firstOrFail();
        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent' => 'Test FC',
            'venue' => 'Test Stadium',
            'kickoff_at' => now()->addDays(7),
            'status' => FixtureStatus::Scheduled,
        ]);

        $source = PointRule::query()->first();

        $txn = $this->engine->credit($player, $fixture, $source);

        $this->assertNotNull($txn);
        $this->assertSame($source->getMorphClass(), $txn->source_type);
        $this->assertSame($source->id, $txn->source_id);
    }

    public function test_adjust_writes_audited_transaction_and_moves_balance(): void
    {
        $player = Candidate::factory()->create(['season_id' => Season::query()->firstOrFail()->id]);
        $admin = User::query()->firstOrFail();

        $txn = $this->engine->adjust($player, 50, 'Welcome bonus', $admin);

        $this->assertSame($player->id, $txn->candidate_id);
        $this->assertSame(PointTransactionType::Adjust, $txn->type);
        $this->assertSame(50, $txn->points);
        $this->assertSame('Welcome bonus', $txn->reason);
        $this->assertSame($admin->id, $txn->created_by);
        $this->assertSame(50, $player->fresh()->pointsBalance());

        $debit = $this->engine->adjust($player, -30, 'Correction', $admin, 'adjust');
        $this->assertSame(-30, $debit->points);
        $this->assertSame(20, $player->fresh()->pointsBalance());
    }

    public function test_adjust_with_reverse_type(): void
    {
        $player = Candidate::factory()->create(['season_id' => Season::query()->firstOrFail()->id]);
        $admin = User::query()->firstOrFail();

        $txn = $this->engine->adjust($player, -10, 'Reversing erroneous credit', $admin, 'reverse');

        $this->assertSame(PointTransactionType::Reverse, $txn->type);
        $this->assertSame(-10, $txn->points);
    }

    public function test_point_rule_seeder_creates_fixed_rule(): void
    {
        $this->assertDatabaseHas('point_rules', [
            'name' => 'Match attendance — 10 pts',
            'type' => PointRuleType::Fixed->value,
            'points' => 10,
            'is_active' => true,
        ]);
    }

    public function test_point_rule_scope_for_fixture_matches_team_and_season(): void
    {
        $team = Team::query()->firstOrFail();
        $season = Season::query()->firstOrFail();
        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent' => 'Test FC',
            'venue' => 'Test Stadium',
            'kickoff_at' => now()->addDays(7),
            'status' => FixtureStatus::Scheduled,
        ]);

        $scoped = PointRule::query()->forFixture($fixture)->get();

        $this->assertGreaterThan(0, $scoped->count());
    }
}
