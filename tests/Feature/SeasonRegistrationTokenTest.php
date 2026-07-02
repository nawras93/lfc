<?php

namespace Tests\Feature;

use App\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeasonRegistrationTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_active_season_has_a_registration_token(): void
    {
        // DatabaseSeeder runs WithoutModelEvents; the seeded active season must
        // still receive a token, otherwise the public registration page 500s.
        $this->seed();

        $season = Season::query()->where('is_active', true)->orderByDesc('id')->firstOrFail();

        $this->assertNotEmpty($season->registration_slug);
        $this->assertNotNull($season->publicRegistrationUrl());
    }

    public function test_saving_a_season_without_a_token_generates_one(): void
    {
        $season = Season::query()->create([
            'name' => 'Token Test Season',
            'is_active' => false,
        ]);

        $this->assertNotEmpty($season->registration_slug, 'creating should generate a token');

        // Force it null (as the muted-events seed path once did) then save again.
        DB::table('seasons')->where('id', $season->id)->update(['registration_slug' => null]);
        $season->refresh();
        $this->assertNull($season->registration_slug);

        $season->touch();
        $season->refresh();
        $this->assertNotEmpty($season->registration_slug, 'saving should backfill a missing token');
    }

    public function test_public_landing_shows_a_clear_message_instead_of_500_when_token_missing(): void
    {
        $this->seed();

        // Simulate a legacy season with no token (bypass the saving hook).
        DB::table('seasons')->update(['registration_slug' => null]);

        $this->get('/')
            ->assertOk()
            ->assertSee(__('public-registration.unavailable.title'));
    }
}
