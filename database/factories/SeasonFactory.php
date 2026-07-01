<?php

namespace Database\Factories;

use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Season>
 */
class SeasonFactory extends Factory
{
    protected $model = Season::class;

    public function definition(): array
    {
        return [
            'name' => '2026/27',
            'is_active' => true,
            'registration_slug' => Str::lower(Str::random(16)),
            'registration_starts_at' => now()->subDay(),
            'registration_ends_at' => now()->addMonth(),
        ];
    }
}
