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
            ['name' => 'LFC U12', 'name_ar' => 'لوسيل تحت ١٢', 'age_group' => 'U12'],
            ['name' => 'LFC U14', 'name_ar' => 'لوسيل تحت ١٤', 'age_group' => 'U14'],
        ] as $team) {
            Team::query()->updateOrCreate(
                ['name' => $team['name']],
                ['name_ar' => $team['name_ar'], 'age_group' => $team['age_group'], 'season_id' => $season->id],
            );
        }
    }
}
