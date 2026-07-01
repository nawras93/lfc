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
        $season = Season::where('is_active', true)->first() ?? Season::query()->first();
        $team = Team::where('name', 'LFC U12')->first() ?? Team::query()->first();

        if ($season === null || $team === null) {
            return;
        }

        Fixture::query()->updateOrCreate(
            ['opponent' => 'Al Sadd SC'],
            [
                'team_id' => $team->id,
                'season_id' => $season->id,
                'venue' => 'Lusail Stadium',
                'kickoff_at' => now()->addDays(7),
                'scan_opens_at' => now()->subDay(),
                'scan_closes_at' => now()->addDays(7),
                'status' => FixtureStatus::OpenForScanning,
            ],
        );

        Fixture::query()->updateOrCreate(
            ['opponent' => 'Al Rayyan SC'],
            [
                'team_id' => $team->id,
                'season_id' => $season->id,
                'venue' => 'Lusail Stadium',
                'kickoff_at' => now()->addDays(14),
                'scan_opens_at' => null,
                'scan_closes_at' => null,
                'status' => FixtureStatus::Scheduled,
            ],
        );
    }
}
