<?php

namespace App\Services;

use App\Enums\DocumentStatus;
use App\Enums\FederationStatus;
use App\Enums\JoiningStatus;
use App\Enums\RecruitmentStage;
use App\Models\Candidate;
use App\Models\Season;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicRegistrationService
{
    public function __construct(
        private readonly CandidateDataNormalizer $candidateDataNormalizer,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Season $season, array $data): Candidate
    {
        $this->ensureNotDuplicate($season, $data['full_name'], $data['parent_phone']);

        $candidate = Candidate::query()->create(
            $this->candidateDataNormalizer->normalize([
                'full_name' => $data['full_name'],
                'playing_position' => $data['playing_position'],
                'year_of_birth' => $data['year_of_birth'],
                'date_of_birth' => $data['date_of_birth'],
                'country_of_birth' => $data['country_of_birth'],
                'citizenship' => $data['citizenship'],
                'year_arrived_qatar' => $data['year_arrived_qatar'],
                'school' => $data['school'],
                'previous_club' => $data['previous_club'],
                'parent_name' => $data['parent_name'],
                'parent_phone' => $data['parent_phone'],
                'parent_whatsapp' => $data['parent_whatsapp'],
                'email' => $data['email'] ?: null,
                'season_id' => $season->id,
                'consent_given' => true,
                'recruitment_stage' => RecruitmentStage::NewApplication,
                'document_status' => DocumentStatus::Pending,
                'qfa_status' => FederationStatus::NotStarted,
                'fifa_status' => FederationStatus::NotStarted,
                'joining_status' => JoiningStatus::NotStarted,
            ]),
        );

        return $candidate;
    }

    public function resolveSeasonFromRegistrationLink(string $registrationSlug): Season
    {
        // registration_slug is a unique, unguessable token and is sufficient to
        // identify the season. The season-name slug in the URL is cosmetic only, so
        // renaming a season never breaks a link that was already shared.
        $season = Season::query()
            ->where('registration_slug', $registrationSlug)
            ->first();

        if ($season === null) {
            throw new NotFoundHttpException;
        }

        return $season;
    }

    private function ensureNotDuplicate(Season $season, string $fullName, string $parentPhone): void
    {
        $exists = Candidate::query()
            ->where('season_id', $season->id)
            ->where('full_name', $fullName)
            ->where('parent_phone', $parentPhone)
            ->exists();

        if (! $exists) {
            return;
        }

        throw ValidationException::withMessages([
            'full_name' => __('public-registration.validation.duplicate'),
        ]);
    }
}
