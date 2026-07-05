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
            ->assertJsonPath('data.0.id', $appTwoNews->id);

        $this->getJson('/api/v1/content/fixtures')
            ->assertOk()
            ->assertJsonPath('data.0.id', $upcoming->id);

        $this->getJson('/api/v1/content/results')
            ->assertOk()
            ->assertJsonPath('data.0.id', $result->id);

        $this->getJson('/api/v1/content/standings')
            ->assertOk()
            ->assertJsonCount(1, 'data');
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
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $upcoming->id);

        $this->getJson('/api/v1/content/results')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $result->id);

        $this->getJson('/api/v1/content/standings')
            ->assertOk()
            ->assertJsonPath('data.0.club_name', 'First Place')
            ->assertJsonPath('data.0.position', 1)
            ->assertJsonPath('data.1.club_name', 'Second Place')
            ->assertJsonPath('data.1.position', 2);
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
            ->assertJsonPath('data.0.club_name', 'نادي لوسيل')
            ->assertJsonPath('data.1.club_name', 'Fallback Club');
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

        $this->assertSame([$appTwoNews->id], NewsResource::getEloquentQuery()->pluck('id')->all());
        $this->assertSame([$appTwoMatch->id], MatchResource::getEloquentQuery()->pluck('id')->all());
        $this->assertSame([$appTwoStanding->id], StandingResource::getEloquentQuery()->pluck('id')->all());

        app(AppContext::class)->clear();

        $this->withHeader('X-App-Key', AppKey::AppOne->value)
            ->getJson('/api/v1/content/news')
            ->assertOk()
            ->assertJsonPath('data.0.id', $appOneNews->id);
    }
}
