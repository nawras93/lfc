<?php

namespace Tests\Feature;

use App\Enums\AppKey;
use App\Enums\FixtureStatus;
use App\Enums\OfferAudience;
use App\Models\Fixture;
use App\Models\NewsPost;
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

    public function test_seeded_rows_keep_app_one_backfill_and_explicit_app_two_stamping(): void
    {
        $this->seed();

        $this->assertSame(2, DB::table('parent_accounts')->where('app', AppKey::AppTwo->value)->count());
        $this->assertSame(8, DB::table('fixtures')->where('app', AppKey::AppTwo->value)->count());
        $this->assertSame(3, DB::table('offers')->where('app', AppKey::AppTwo->value)->count());
        $this->assertSame(5, DB::table('point_transactions')->where('unit', '!=', 'points')->count());

        $this->assertSame(AppKey::AppOne->value, DB::table('parent_accounts')->where('email', 'parent.demo@lfc.test')->value('app'));
        $this->assertSame(AppKey::AppOne->value, DB::table('fixtures')->where('opponent', 'Al Sadd SC')->value('app'));
        $this->assertSame(AppKey::AppOne->value, DB::table('offers')->where('title', 'Early Bird Registration Discount')->value('app'));
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

        $this->assertContains($appTwoAccount->id, ParentAccount::query()->pluck('id')->all());
        $this->assertContains($appTwoFixture->id, Fixture::query()->pluck('id')->all());
        $this->assertContains($appTwoOffer->id, Offer::query()->pluck('id')->all());
        $this->assertTrue(ParentAccount::query()->get()->every(fn (ParentAccount $account): bool => $account->app === AppKey::AppTwo));
        $this->assertTrue(Fixture::query()->get()->every(fn (Fixture $fixture): bool => $fixture->app === AppKey::AppTwo));
        $this->assertTrue(Offer::query()->get()->every(fn (Offer $offer): bool => $offer->app === AppKey::AppTwo));

        $this->assertContains($appTwoAccount->id, ParentAccount::query()->forApp(AppKey::AppTwo)->pluck('id')->all());
        $this->assertContains($appTwoFixture->id, Fixture::query()->forApp(AppKey::AppTwo)->pluck('id')->all());
        $this->assertContains($appTwoOffer->id, Offer::query()->forApp(AppKey::AppTwo)->pluck('id')->all());

        $this->assertGreaterThan(1, ParentAccount::withoutAppScope()->count());
        $this->assertGreaterThan(1, Fixture::withoutAppScope()->count());
        $this->assertGreaterThan(1, Offer::withoutAppScope()->count());

        $context->setCurrent(AppKey::AppOne);

        $this->assertNotContains($appTwoAccount->id, ParentAccount::query()->pluck('id')->all());
        $this->assertNotContains($appTwoFixture->id, Fixture::query()->pluck('id')->all());
        $this->assertNotContains($appTwoOffer->id, Offer::query()->pluck('id')->all());

        $this->assertContains($appTwoAccount->id, ParentAccount::withoutAppScope()->forApp(AppKey::AppTwo)->pluck('id')->all());
        $this->assertContains($appTwoFixture->id, Fixture::withoutAppScope()->forApp(AppKey::AppTwo)->pluck('id')->all());
        $this->assertContains($appTwoOffer->id, Offer::withoutAppScope()->forApp(AppKey::AppTwo)->pluck('id')->all());
    }

    public function test_scoped_models_auto_stamp_app_on_create_when_context_exists_and_fall_back_to_db_default_without_context(): void
    {
        $this->seed();

        app(AppContext::class)->setCurrent(AppKey::AppTwo);

        $offer = Offer::query()->create([
            'title' => 'Scoped Offer',
            'body' => 'Scoped body',
            'audience' => OfferAudience::All,
            'is_published' => true,
        ]);

        $news = NewsPost::query()->create([
            'title' => 'Scoped News',
            'body' => 'Scoped news body',
        ]);

        $this->assertSame(AppKey::AppTwo, $offer->fresh()->app);
        $this->assertSame(AppKey::AppTwo, $news->fresh()->app);

        app(AppContext::class)->clear();

        $defaultNews = NewsPost::query()->create([
            'title' => 'Default News',
            'body' => 'Default body',
        ]);

        $this->assertSame(AppKey::AppOne, $defaultNews->fresh()->app);
    }
}
