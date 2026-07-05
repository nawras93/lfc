<?php

namespace Tests\Feature;

use App\Enums\AppKey;
use App\Enums\FixtureStatus;
use App\Enums\OfferAudience;
use App\Models\Fixture;
use App\Models\Offer;
use App\Models\ParentAccount;
use App\Models\Season;
use App\Models\Team;
use App\Support\AppContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AppScopingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        app(AppContext::class)->clear();

        parent::tearDown();
    }

    public function test_seeded_rows_backfill_to_app_one_and_points_unit(): void
    {
        $this->seed();

        $this->assertSame(0, DB::table('parent_accounts')->where('app', '!=', AppKey::AppOne->value)->count());
        $this->assertSame(0, DB::table('fixtures')->where('app', '!=', AppKey::AppOne->value)->count());
        $this->assertSame(0, DB::table('offers')->where('app', '!=', AppKey::AppOne->value)->count());
        $this->assertSame(0, DB::table('point_transactions')->where('unit', '!=', 'points')->count());
    }

    public function test_global_scope_is_inert_when_no_app_context_is_set(): void
    {
        $this->seed();

        $this->assertSame(DB::table('parent_accounts')->count(), ParentAccount::query()->count());
        $this->assertSame(DB::table('fixtures')->count(), Fixture::query()->count());
        $this->assertSame(DB::table('offers')->count(), Offer::query()->count());
    }

    public function test_app_context_isolates_queries_and_can_be_bypassed(): void
    {
        $this->seed();

        $appTwoAccount = ParentAccount::factory()->create([
            'email' => 'app-two-parent@lfc.test',
            'app' => AppKey::AppTwo,
        ]);

        $team = Team::query()->firstOrFail();
        $season = Season::query()->firstOrFail();

        $appTwoFixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent' => 'App Two Opponent',
            'venue' => 'Supporters Gate',
            'kickoff_at' => now()->addDay(),
            'status' => FixtureStatus::Scheduled,
            'app' => AppKey::AppTwo,
        ]);

        $appTwoOffer = Offer::query()->create([
            'title' => 'App Two Offer',
            'body' => 'Fan-app only.',
            'audience' => OfferAudience::All,
            'app' => AppKey::AppTwo,
            'is_published' => true,
        ]);

        $context = app(AppContext::class);
        $context->setCurrent(AppKey::AppTwo);

        $this->assertSame([$appTwoAccount->id], ParentAccount::query()->pluck('id')->all());
        $this->assertSame([$appTwoFixture->id], Fixture::query()->pluck('id')->all());
        $this->assertSame([$appTwoOffer->id], Offer::query()->pluck('id')->all());

        $this->assertSame([$appTwoAccount->id], ParentAccount::query()->forApp(AppKey::AppTwo)->pluck('id')->all());
        $this->assertSame([$appTwoFixture->id], Fixture::query()->forApp(AppKey::AppTwo)->pluck('id')->all());
        $this->assertSame([$appTwoOffer->id], Offer::query()->forApp(AppKey::AppTwo)->pluck('id')->all());

        $this->assertGreaterThan(1, ParentAccount::withoutAppScope()->count());
        $this->assertGreaterThan(1, Fixture::withoutAppScope()->count());
        $this->assertGreaterThan(1, Offer::withoutAppScope()->count());

        $context->setCurrent(AppKey::AppOne);

        $this->assertNotContains($appTwoAccount->id, ParentAccount::query()->pluck('id')->all());
        $this->assertNotContains($appTwoFixture->id, Fixture::query()->pluck('id')->all());
        $this->assertNotContains($appTwoOffer->id, Offer::query()->pluck('id')->all());

        $this->assertSame([$appTwoAccount->id], ParentAccount::withoutAppScope()->forApp(AppKey::AppTwo)->pluck('id')->all());
        $this->assertSame([$appTwoFixture->id], Fixture::withoutAppScope()->forApp(AppKey::AppTwo)->pluck('id')->all());
        $this->assertSame([$appTwoOffer->id], Offer::withoutAppScope()->forApp(AppKey::AppTwo)->pluck('id')->all());
    }
}
