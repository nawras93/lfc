<?php

namespace Tests\Feature;

use App\Enums\FixtureStatus;
use App\Enums\OfferAudience;
use App\Enums\PlayingPosition;
use App\Enums\RecruitmentStage;
use App\Enums\RedemptionType;
use App\Enums\DocumentStatus;
use App\Models\Candidate;
use App\Models\Fixture;
use App\Models\Offer;
use App\Models\ParentAccount;
use App\Models\RedemptionItem;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiLocaleNegotiationTest extends TestCase
{
    use RefreshDatabase;

    public function test_offers_follow_accept_language_and_default_to_english(): void
    {
        $this->seed();

        $parent = ParentAccount::factory()->create();
        $token = $parent->createToken('mobile')->plainTextToken;

        $offer = Offer::query()->create([
            'title' => 'Early Registration',
            'title_ar' => 'التسجيل المبكر',
            'body' => 'English offer body',
            'body_ar' => 'وصف العرض بالعربية',
            'audience' => OfferAudience::All,
            'is_published' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
        ]);

        $this->withToken($token)
            ->withHeader('Accept-Language', 'ar')
            ->getJson('/api/v1/offers')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $offer->id,
                'title' => 'التسجيل المبكر',
                'body' => 'وصف العرض بالعربية',
            ]);

        $this->withToken($token)
            ->withHeader('Accept-Language', 'en')
            ->getJson('/api/v1/offers')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $offer->id,
                'title' => 'Early Registration',
                'body' => 'English offer body',
            ]);

        $this->withToken($token)
            ->getJson('/api/v1/offers')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $offer->id,
                'title' => 'Early Registration',
                'body' => 'English offer body',
            ]);
    }

    public function test_redemption_items_follow_accept_language(): void
    {
        $this->seed();

        $parent = ParentAccount::factory()->create();
        $token = $parent->createToken('mobile')->plainTextToken;

        $item = RedemptionItem::query()->create([
            'name' => 'Training Shirt',
            'name_ar' => 'قميص التدريب',
            'description' => 'English reward description',
            'description_ar' => 'وصف المكافأة بالعربية',
            'type' => RedemptionType::Merch,
            'points_cost' => 150,
            'stock' => 5,
            'is_active' => true,
        ]);

        $this->withToken($token)
            ->withHeader('Accept-Language', 'ar')
            ->getJson('/api/v1/redemption-items')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $item->id,
                'name' => 'قميص التدريب',
                'description' => 'وصف المكافأة بالعربية',
            ]);

        $this->withToken($token)
            ->withHeader('Accept-Language', 'en')
            ->getJson('/api/v1/redemption-items')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $item->id,
                'name' => 'Training Shirt',
                'description' => 'English reward description',
            ]);
    }

    public function test_players_localize_team_name_and_enum_labels(): void
    {
        $this->seed();

        $season = Season::query()->firstOrFail();
        $team = Team::factory()->create([
            'season_id' => $season->id,
            'name' => 'LFC U14',
            'name_ar' => 'لفك تحت 14',
        ]);

        $parent = ParentAccount::factory()->create();
        $player = Candidate::factory()->create([
            'season_id' => $season->id,
            'team_id' => $team->id,
            'full_name' => 'Locale Player',
            'is_player' => true,
            'playing_position' => PlayingPosition::Goalkeeper,
            'recruitment_stage' => RecruitmentStage::Accepted,
            'document_status' => DocumentStatus::Complete,
        ]);
        $parent->players()->attach($player);

        $token = $parent->createToken('mobile')->plainTextToken;

        $this->withToken($token)
            ->withHeader('Accept-Language', 'ar')
            ->getJson('/api/v1/players')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $player->id,
                'team_name' => 'لفك تحت 14',
                'playing_position' => 'حارس مرمى',
                'progress' => 'مقبول',
            ]);

        $this->withToken($token)
            ->withHeader('Accept-Language', 'en')
            ->getJson('/api/v1/players')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $player->id,
                'team_name' => 'LFC U14',
                'playing_position' => 'Goalkeeper',
                'progress' => 'Accepted',
            ]);
    }

    public function test_query_lang_overrides_header_and_regional_header_resolves(): void
    {
        $this->seed();

        $parent = ParentAccount::factory()->create();
        $token = $parent->createToken('mobile')->plainTextToken;

        $offer = Offer::query()->create([
            'title' => 'Header vs Query',
            'title_ar' => 'ترويسة مقابل الاستعلام',
            'body' => 'English body',
            'body_ar' => 'المحتوى العربي',
            'audience' => OfferAudience::All,
            'is_published' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDay(),
        ]);

        $this->withToken($token)
            ->withHeader('Accept-Language', 'en;q=0.9,ar-QA;q=0.8')
            ->getJson('/api/v1/offers?lang=ar')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $offer->id,
                'title' => 'ترويسة مقابل الاستعلام',
            ]);

        $this->withToken($token)
            ->withHeader('Accept-Language', 'ar-QA, en;q=0.8')
            ->getJson('/api/v1/offers')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $offer->id,
                'title' => 'ترويسة مقابل الاستعلام',
            ]);
    }

    public function test_staff_fixtures_localize_team_name(): void
    {
        $this->seed();

        $team = Team::query()->firstOrFail();
        $team->update([
            'name' => 'LFC U12',
            'name_ar' => 'لفك تحت 12',
        ]);

        $season = Season::query()->firstOrFail();

        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $season->id,
            'opponent' => 'Doha FC',
            'venue' => 'Pitch 1',
            'kickoff_at' => now()->addHour(),
            'scan_opens_at' => now()->subHour(),
            'scan_closes_at' => now()->addHours(2),
            'status' => FixtureStatus::OpenForScanning,
        ]);

        $response = $this->postJson('/api/v1/staff/login', [
            'email' => env('LFC_ADMIN_EMAIL', 'admin@lfc.test'),
            'password' => env('LFC_ADMIN_PASSWORD', 'password'),
        ])->assertOk();

        $staffToken = $response->json('token');

        $this->withToken($staffToken)
            ->withHeader('Accept-Language', 'ar')
            ->getJson('/api/v1/staff/fixtures')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $fixture->id,
                'team_name' => 'لفك تحت 12',
            ]);

        $this->withToken($staffToken)
            ->withHeader('Accept-Language', 'en')
            ->getJson('/api/v1/staff/fixtures')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $fixture->id,
                'team_name' => 'LFC U12',
            ]);
    }
}
