<?php

namespace Database\Factories;

use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'name' => 'LFC U12',
            'age_group' => 'U12',
            'season_id' => Season::factory(),
        ];
    }
}
