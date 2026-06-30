<?php

namespace Database\Seeders;

use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $season = Season::query()->firstOrFail();

        foreach ([
            ['name' => 'LFC U12', 'age_group' => 'U12'],
            ['name' => 'LFC U14', 'age_group' => 'U14'],
        ] as $team) {
            Team::query()->updateOrCreate(
                ['name' => $team['name']],
                ['age_group' => $team['age_group'], 'season_id' => $season->id],
            );
        }
    }
}
