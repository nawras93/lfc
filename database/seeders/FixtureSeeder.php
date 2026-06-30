<?php

namespace Database\Seeders;

use App\Enums\FixtureStatus;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Seeder;

class FixtureSeeder extends Seeder
{
    public function run(): void
    {
        $season = Season::query()->firstOrCreate(
            ['name' => '2026/27'],
            ['is_active' => true],
        );

        $team = Team::query()->firstOrCreate(
            ['name' => 'LFC U12'],
            ['age_group' => 'U12', 'season_id' => $season->id],
        );

        Fixture::query()->updateOrCreate(
            ['opponent' => 'Al Sadd SC'],
            [
                'team_id' => $team->id,
                'season_id' => $team->season_id,
                'venue' => 'Lusail Stadium',
                'kickoff_at' => now()->addDays(7),
                'scan_opens_at' => now()->subHour(),
                'scan_closes_at' => now()->addHours(3),
                'status' => FixtureStatus::OpenForScanning,
            ],
        );

        Fixture::query()->updateOrCreate(
            ['opponent' => 'Al Rayyan SC'],
            [
                'team_id' => $team->id,
                'season_id' => $team->season_id,
                'venue' => 'Lusail Stadium',
                'kickoff_at' => now()->addDays(14),
                'scan_opens_at' => null,
                'scan_closes_at' => null,
                'status' => FixtureStatus::Scheduled,
            ],
        );
    }
}
