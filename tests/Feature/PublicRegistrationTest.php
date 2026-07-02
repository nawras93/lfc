<?php

namespace Tests\Feature;

use App\Filament\Resources\Candidates\CandidateResource;
use App\Models\Candidate;
use App\Models\Season;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_home_requires_a_season_specific_link(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeText('Registration link required');
        $response->assertDontSee('name="full_name"', false);
    }

    public function test_registration_page_renders_in_english_and_arabic_with_rtl(): void
    {
        $season = Season::factory()->create();

        $english = $this->get($this->registrationUrl($season));
        $english->assertOk();
        $english->assertSee('dir="ltr"', false);
        $english->assertSeeText('Candidate details');

        $arabic = $this->get($this->registrationUrl($season, 'ar'));
        $arabic->assertOk();
        $arabic->assertSee('dir="rtl"', false);
        $arabic->assertSeeText('بيانات اللاعب');
    }

    public function test_successful_submission_creates_candidate_for_the_linked_season_with_default_workflow_values(): void
    {
        $activeSeason = Season::factory()->create([
            'name' => '2025/26',
            'is_active' => true,
        ]);

        $registrationSeason = Season::factory()->create([
            'name' => '2026/27',
            'is_active' => false,
        ]);

        $response = $this->post($this->registrationUrl($registrationSeason), $this->payload());

        $response->assertRedirect($this->registrationUrl($registrationSeason));

        $candidate = Candidate::query()->firstOrFail();

        $this->assertSame($registrationSeason->id, $candidate->season_id);
        $this->assertNotSame($activeSeason->id, $candidate->season_id);
        $this->assertTrue($candidate->consent_given);
        $this->assertNotNull($candidate->consent_at);
        $this->assertSame('new_application', $candidate->recruitment_stage->value);
        $this->assertSame('pending', $candidate->document_status->value);
        $this->assertSame('not_started', $candidate->qfa_status->value);
        $this->assertSame('not_started', $candidate->fifa_status->value);
        $this->assertSame('not_started', $candidate->joining_status->value);
        $this->assertTrue(CandidateResource::getEloquentQuery()->whereKey($candidate->id)->exists());
    }

    // TEMP: the consent section is hidden in the public form, so consent is
    // auto-applied server-side rather than required from a checkbox. When the
    // section is restored, revert this to assert consent is required.
    public function test_consent_is_auto_applied_while_section_is_hidden(): void
    {
        $season = Season::factory()->create();

        $response = $this->from($this->registrationUrl($season))->post($this->registrationUrl($season), array_merge(
            $this->payload(),
            ['consent_given' => ''],
        ));

        $response->assertRedirect($this->registrationUrl($season));
        $response->assertSessionHasNoErrors();
        $this->assertTrue(Candidate::query()->firstOrFail()->consent_given);
    }

    public function test_email_is_required(): void
    {
        $season = Season::factory()->create();

        $response = $this->from($this->registrationUrl($season))->post($this->registrationUrl($season), array_merge(
            $this->payload(),
            ['email' => ''],
        ));

        $response->assertRedirect($this->registrationUrl($season));
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('candidates', 0);
    }

    public function test_duplicate_submission_for_same_season_is_rejected(): void
    {
        $season = Season::factory()->create();

        Candidate::factory()->create([
            'season_id' => $season->id,
            'full_name' => 'Player One',
            'parent_phone' => '+974 5555 0001',
        ]);

        $response = $this->from($this->registrationUrl($season))->post($this->registrationUrl($season), $this->payload());

        $response->assertRedirect($this->registrationUrl($season));
        $response->assertSessionHasErrors('full_name');
        $this->assertDatabaseCount('candidates', 1);
    }

    public function test_arabic_full_name_is_rejected_with_validation_error(): void
    {
        $season = Season::factory()->create();

        $response = $this->from($this->registrationUrl($season))->post($this->registrationUrl($season), array_merge(
            $this->payload(),
            ['full_name' => 'يوسف'],
        ));

        $response->assertRedirect($this->registrationUrl($season));
        $response->assertSessionHasErrors('full_name');
        $this->assertDatabaseCount('candidates', 0);
    }

    public function test_validation_errors_render_summary_and_highlight_offending_field(): void
    {
        $season = Season::factory()->create();

        $response = $this->from($this->registrationUrl($season))
            ->followingRedirects()
            ->post($this->registrationUrl($season), array_merge(
                $this->payload(),
                ['full_name' => 'يوسف'],
            ));

        $response->assertOk();
        // Summary alert copy at the top of the form card.
        $response->assertSeeText('Please check your registration');
        // Field-aware message names the field (injected :attribute).
        $response->assertSeeText('Please enter Player full name in Latin (English) characters.');
        // The offending field is visually highlighted.
        $response->assertSee('lfc-field-error', false);
    }

    public function test_registration_link_is_closed_outside_configured_window(): void
    {
        $season = Season::factory()->create([
            'registration_starts_at' => now()->subDays(10),
            'registration_ends_at' => now()->subDay(),
        ]);

        $getResponse = $this->get($this->registrationUrl($season));
        $getResponse->assertOk();
        $getResponse->assertSeeText('Registration is closed');
        $getResponse->assertDontSee('name="full_name"', false);

        $postResponse = $this->from($this->registrationUrl($season))->post($this->registrationUrl($season), $this->payload());
        $postResponse->assertRedirect($this->registrationUrl($season));
        $postResponse->assertSessionHasErrors('registration');
        $this->assertDatabaseCount('candidates', 0);
    }

    private function registrationUrl(Season $season, string $locale = 'en'): string
    {
        return route('public.register.show', [
            'seasonSlug' => $season->registrationSeasonSlug(),
            'registrationSlug' => $season->registration_slug,
            'lang' => $locale,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'full_name' => 'Player One',
            'playing_position' => 'midfielder',
            'year_of_birth' => 2014,
            'date_of_birth' => '2014-05-10',
            'country_of_birth' => 'Qatar',
            'citizenship' => 'Qatari',
            'year_arrived_qatar' => 2018,
            'school' => 'LFC School',
            'previous_club' => 'None',
            'parent_name' => 'Parent One',
            'parent_phone' => '+974 5555 0001',
            'parent_whatsapp' => '+974 5555 0002',
            'email' => 'parent@example.com',
            'consent_given' => '1',
        ];
    }
}
