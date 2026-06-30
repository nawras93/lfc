<?php

namespace Database\Factories;

use App\Enums\DocumentStatus;
use App\Enums\FederationStatus;
use App\Enums\JoiningStatus;
use App\Enums\PlayingPosition;
use App\Enums\RecruitmentStage;
use App\Models\Candidate;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Candidate>
 */
class CandidateFactory extends Factory
{
    protected $model = Candidate::class;

    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'year_of_birth' => 2014,
            'date_of_birth' => '2014-05-10',
            'country_of_birth' => 'Qatar',
            'citizenship' => 'Qatari',
            'year_arrived_qatar' => 2014,
            'playing_position' => PlayingPosition::Midfielder,
            'school' => 'Lusail Academy School',
            'previous_club' => 'None',
            'parent_name' => fake()->name(),
            'parent_phone' => fake()->unique()->numerify('555#######'),
            'parent_whatsapp' => fake()->unique()->numerify('555#######'),
            'email' => fake()->safeEmail(),
            'notes' => null,
            'season_id' => Season::factory(),
            'team_id' => null,
            'is_player' => false,
            'consent_given' => false,
            'consent_at' => null,
            'recruitment_stage' => RecruitmentStage::NewApplication,
            'document_status' => DocumentStatus::Pending,
            'qfa_status' => FederationStatus::NotStarted,
            'fifa_status' => FederationStatus::NotStarted,
            'joining_status' => JoiningStatus::NotStarted,
            'status_updated_at' => null,
            'status_updated_by' => null,
        ];
    }
}
