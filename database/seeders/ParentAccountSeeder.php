<?php

namespace Database\Seeders;

use App\Enums\DocumentStatus;
use App\Enums\JoiningStatus;
use App\Enums\PointTransactionType;
use App\Enums\RecruitmentStage;
use App\Models\Candidate;
use App\Models\ParentAccount;
use App\Models\PointTransaction;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Seeder;

class ParentAccountSeeder extends Seeder
{
    public function run(): void
    {
        $season = Season::query()->first();
        $team = Team::query()->first();

        if (! $season || ! $team) {
            return;
        }

        $candidate = Candidate::query()->updateOrCreate(
            ['email' => env('LFC_DEMO_PARENT_EMAIL', 'parent.demo@lfc.test')],
            [
                'full_name' => 'Omar Demo',
                'year_of_birth' => 2014,
                'date_of_birth' => '2014-03-14',
                'country_of_birth' => 'Qatar',
                'citizenship' => 'Qatari',
                'year_arrived_qatar' => 2014,
                'playing_position' => 'midfielder',
                'school' => 'Lusail Demo School',
                'previous_club' => 'None',
                'parent_name' => 'Amina Demo',
                'parent_phone' => '555100100',
                'parent_whatsapp' => '555100101',
                'notes' => 'Seeded demo player for the parent API slice.',
                'season_id' => $season->id,
                'team_id' => $team->id,
                'is_player' => true,
                'consent_given' => true,
                'consent_at' => now(),
                'recruitment_stage' => RecruitmentStage::Accepted,
                'document_status' => DocumentStatus::Complete,
                'qfa_status' => 'not_started',
                'fifa_status' => 'not_started',
                'joining_status' => JoiningStatus::ReadyToJoin,
            ],
        );

        $parent = ParentAccount::query()->updateOrCreate(
            ['email' => env('LFC_DEMO_PARENT_EMAIL', 'parent.demo@lfc.test')],
            [
                'name' => 'Amina Demo',
                'password' => env('LFC_DEMO_PARENT_PASSWORD', 'password'),
                'phone' => '555100100',
                'whatsapp' => '555100101',
                'accepted_at' => now(),
                'invitation_token' => null,
                'invited_at' => now(),
            ],
        );

        $parent->players()->syncWithoutDetaching([$candidate->id]);

        // Give the demo player starting points so they can redeem items
        PointTransaction::query()->firstOrCreate(
            [
                'candidate_id' => $candidate->id,
                'type' => PointTransactionType::Adjust,
                'reason' => 'Demo account — starting points',
            ],
            [
                'points' => 150,
            ],
        );
    }
}
