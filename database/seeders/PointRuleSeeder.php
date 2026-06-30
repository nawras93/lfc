<?php

namespace Database\Seeders;

use App\Enums\PointRuleType;
use App\Models\PointRule;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Seeder;

class PointRuleSeeder extends Seeder
{
    public function run(): void
    {
        PointRule::query()->updateOrCreate(
            ['name' => 'Match attendance — 10 pts'],
            [
                'type' => PointRuleType::Fixed,
                'points' => 10,
                'team_id' => null,
                'season_id' => null,
                'priority' => 0,
                'is_active' => true,
                'starts_at' => null,
                'ends_at' => null,
            ],
        );

        $team = Team::query()->first();

        if ($team !== null) {
            PointRule::query()->updateOrCreate(
                ['name' => 'Bonus attendance — 5% of fee'],
                [
                    'type' => PointRuleType::Percentage,
                    'percentage' => 5.00,
                    'base_amount' => 200.00,
                    'team_id' => $team->id,
                    'season_id' => null,
                    'priority' => 10,
                    'is_active' => true,
                    'starts_at' => null,
                    'ends_at' => null,
                ],
            );
        }
    }
}
