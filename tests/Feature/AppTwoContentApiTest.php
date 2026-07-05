<?php

namespace Tests\Feature;

use App\Enums\AppKey;
use App\Enums\FixtureStatus;
use App\Filament\AppTwo\Resources\Matches\MatchResource;
use App\Filament\AppTwo\Resources\News\NewsResource;
use App\Filament\AppTwo\Resources\Standings\StandingResource;
use App\Models\Fixture;
use App\Models\NewsPost;
use App\Models\Season;
use App\Models\Standing;
use App\Models\Team;
use App\Support\AppContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppTwoContentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    protected function tearDown(): void
    {
        app(AppContext::class)->clear();

        parent::tearDown();
    }

    public function test_guest_content_endpoints_are_public_and_default_to_app_two_scope(): void
    {
        $appTwoNews = NewsPost::query()->create([
            'app' => AppKey::AppTwo,
            'title' => 'App Two Story',
            'body' => 'Visible to guests',
            'is_published' => true,
            'published_at' => now()->subHour(),
        ]);

        NewsPost::query()->create([
            'app' => AppKey::AppOne,
            'title' => 'App One Story',
            'body' => 'Should stay hidden by default',
            'is_published' => true,
            'published_at' => now()->subHour(),
        ]);

        $upcoming = Fixture::query()->create([
            'app' => AppKey::AppTwo,
            'team_id' => null,
            'season_id' => null,
            'opponent' => 'Future Opponent',
            'competition' => 'Stars League',
            'is_home' => true,
            'venue' => 'Lusail Stadium',
            'kickoff_at' => now()->addDay(),
            'status' => FixtureStatus::Scheduled,
        ]);

        $result = Fixture::query()->create([
            'app' => AppKey::AppTwo,
            'team_id' => null,
            'season_id' => null,
            'opponent' => 'Finished Opponent',
            'competition' => 'Stars League',
            'is_home' => false,
            'venue' => 'Away Ground',
            'kickoff_at' => now()->subDay(),
            'status' => FixtureStatus::Closed,
            'our_score' => 3,
            'opponent_score' => 1,
        ]);

        Standing::query()->create([
            'app' => AppKey::AppTwo,
            'club_name' => 'Lusail SC',
            'played' => 1,
            'won' => 1,
            'goals_for' => 3,
            'goals_against' => 1,
            'points' => 3,
            'is_own_club' => true,
        ]);

        $this->getJson('/api/v1/content/news')
            ->assertOk()
            ->assertJsonFragment(['id' => $appTwoNews->id, 'title' => 'App Two Story'])
            ->assertJsonMissing(['title' => 'App One Story']);

        $this->getJson('/api/v1/content/fixtures')
            ->assertOk()
            ->assertJsonFragment(['id' => $upcoming->id, 'opponent' => 'Future Opponent']);

        $this->getJson('/api/v1/content/results')
            ->assertOk()
            ->assertJsonFragment(['id' => $result->id, 'opponent' => 'Finished Opponent']);

        $this->getJson('/api/v1/content/standings')
            ->assertOk()
            ->assertJsonFragment(['club_name' => 'Lusail SC']);
    }

    public function test_unpublished_or_future_news_is_hidden_and_show_404s_for_non_public_rows(): void
    {
        $published = NewsPost::query()->create([
            'app' => AppKey::AppTwo,
            'title' => 'Published Story',
            'body' => 'Visible body',
            'is_published' => true,
            'published_at' => now()->subHour(),
        ]);

        $draft = NewsPost::query()->create([
            'app' => AppKey::AppTwo,
            'title' => 'Draft Story',
            'body' => 'Hidden body',
            'is_published' => false,
        ]);

        $scheduled = NewsPost::query()->create([
            'app' => AppKey::AppTwo,
            'title' => 'Future Story',
            'body' => 'Future body',
            'is_published' => true,
            'published_at' => now()->addHour(),
        ]);

        $this->getJson('/api/v1/content/news')
            ->assertOk()
            ->assertJsonFragment(['id' => $published->id, 'title' => 'Published Story'])
            ->assertJsonMissing(['id' => $draft->id])
            ->assertJsonMissing(['id' => $scheduled->id]);

        $this->getJson('/api/v1/content/news/'.$published->id)
            ->assertOk()
            ->assertJsonPath('data.body', 'Visible body');

        $this->getJson('/api/v1/content/news/'.$draft->id)->assertNotFound();
        $this->getJson('/api/v1/content/news/'.$scheduled->id)->assertNotFound();
    }

    public function test_fixtures_and_results_are_partitioned_and_standings_are_ordered_with_positions(): void
    {
        $upcoming = Fixture::query()->create([
            'app' => AppKey::AppTwo,
            'team_id' => null,
            'opponent' => 'Upcoming Match',
            'competition' => 'League',
            'is_home' => true,
            'venue' => 'Pitch 1',
            'kickoff_at' => now()->addDays(2),
            'status' => FixtureStatus::Scheduled,
        ]);

        Fixture::query()->create([
            'app' => AppKey::AppTwo,
            'team_id' => null,
            'opponent' => 'Past Without Score',
            'competition' => 'League',
            'is_home' => true,
            'venue' => 'Pitch 2',
            'kickoff_at' => now()->subDay(),
            'status' => FixtureStatus::Closed,
        ]);

        $result = Fixture::query()->create([
            'app' => AppKey::AppTwo,
            'team_id' => null,
            'opponent' => 'Played Match',
            'competition' => 'Cup',
            'is_home' => false,
            'venue' => 'Away',
            'kickoff_at' => now()->subHours(2),
            'status' => FixtureStatus::Closed,
            'our_score' => 2,
            'opponent_score' => 0,
        ]);

        Standing::query()->create([
            'app' => AppKey::AppTwo,
            'club_name' => 'Second Place',
            'played' => 2,
            'won' => 1,
            'drawn' => 1,
            'goals_for' => 2,
            'goals_against' => 1,
            'points' => 4,
        ]);

        Standing::query()->create([
            'app' => AppKey::AppTwo,
            'club_name' => 'First Place',
            'played' => 2,
            'won' => 2,
            'goals_for' => 5,
            'goals_against' => 1,
            'points' => 6,
        ]);

        $this->getJson('/api/v1/content/fixtures')
            ->assertOk()
            ->assertJsonFragment(['id' => $upcoming->id, 'opponent' => 'Upcoming Match'])
            ->assertJsonMissing(['id' => $result->id, 'opponent' => 'Played Match']);

        $this->getJson('/api/v1/content/results')
            ->assertOk()
            ->assertJsonFragment(['id' => $result->id, 'opponent' => 'Played Match'])
            ->assertJsonMissing(['id' => $upcoming->id, 'opponent' => 'Upcoming Match']);

        $standings = $this->getJson('/api/v1/content/standings')
            ->assertOk()
            ->json('data');

        $first = collect($standings)->firstWhere('club_name', 'First Place');
        $second = collect($standings)->firstWhere('club_name', 'Second Place');

        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertGreaterThan($first['position'], $second['position']);
    }

    public function test_content_endpoints_localize_arabic_fields_with_fallbacks(): void
    {
        NewsPost::query()->create([
            'app' => AppKey::AppTwo,
            'title' => 'English News',
            'title_ar' => 'خبر عربي',
            'excerpt' => 'English excerpt',
            'excerpt_ar' => 'مقتطف عربي',
            'body' => 'English body',
            'body_ar' => 'نص عربي',
            'is_published' => true,
            'published_at' => now()->subHour(),
        ]);

        Standing::query()->create([
            'app' => AppKey::AppTwo,
            'club_name' => 'Lusail SC',
            'club_name_ar' => 'نادي لوسيل',
            'points' => 1,
        ]);

        Standing::query()->create([
            'app' => AppKey::AppTwo,
            'club_name' => 'Fallback Club',
            'club_name_ar' => null,
            'points' => 0,
        ]);

        $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/v1/content/news')
            ->assertOk()
            ->assertJsonFragment([
                'title' => 'خبر عربي',
                'excerpt' => 'مقتطف عربي',
            ]);

        $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/v1/content/standings')
            ->assertOk()
            ->assertJsonFragment(['club_name' => 'نادي لوسيل'])
            ->assertJsonFragment(['club_name' => 'Fallback Club']);
    }

    public function test_content_header_can_switch_scope_and_resources_stay_app_two_scoped(): void
    {
        $season = Season::query()->firstOrFail();
        $team = Team::query()->firstOrFail();

        $appOneNews = NewsPost::query()->create([
            'app' => AppKey::AppOne,
            'title' => 'App One News',
            'body' => 'Hidden unless app_one requested',
            'is_published' => true,
            'published_at' => now()->subHour(),
        ]);

        $appTwoNews = NewsPost::query()->create([
            'app' => AppKey::AppTwo,
            'title' => 'App Two News',
            'body' => 'Default guest content',
            'is_published' => true,
            'published_at' => now()->subHour(),
        ]);

        $appTwoMatch = Fixture::query()->create([
            'app' => AppKey::AppTwo,
            'team_id' => null,
            'season_id' => $season->id,
            'opponent' => 'Visible Match',
            'competition' => 'League',
            'is_home' => true,
            'venue' => 'Lusail Stadium',
            'kickoff_at' => now()->addDay(),
            'status' => FixtureStatus::Scheduled,
        ]);

        Fixture::query()->create([
            'app' => AppKey::AppTwo,
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent' => 'Academy Fixture',
            'venue' => 'Academy Pitch',
            'kickoff_at' => now()->addDays(3),
            'status' => FixtureStatus::Scheduled,
        ]);

        $appTwoStanding = Standing::query()->create([
            'app' => AppKey::AppTwo,
            'club_name' => 'Visible Club',
            'points' => 1,
        ]);

        app(AppContext::class)->setCurrent(AppKey::AppTwo);

        $this->assertContains($appTwoNews->id, NewsResource::getEloquentQuery()->pluck('id')->all());
        $this->assertContains($appTwoMatch->id, MatchResource::getEloquentQuery()->pluck('id')->all());
        $this->assertContains($appTwoStanding->id, StandingResource::getEloquentQuery()->pluck('id')->all());
        $this->assertNotContains($appOneNews->id, NewsResource::getEloquentQuery()->pluck('id')->all());

        app(AppContext::class)->clear();

        $this->withHeader('X-App-Key', AppKey::AppOne->value)
            ->getJson('/api/v1/content/news')
            ->assertOk()
            ->assertJsonFragment(['id' => $appOneNews->id, 'title' => 'App One News']);
    }
}
