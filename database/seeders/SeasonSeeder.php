<?php

namespace Database\Seeders;

use App\Models\Season;
use Illuminate\Database\Seeder;

class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        // Intentionally omit registration_slug: the model's creating hook generates
        // it once on first insert. Keeping it out of the update payload means the
        // shared public registration link stays stable across re-seeds instead of
        // being rotated (and broken) on every run.
        Season::query()->updateOrCreate(
            ['name' => '2026/27'],
            [
                'is_active' => true,
                'registration_starts_at' => now()->subWeek(),
                'registration_ends_at' => now()->addMonths(2),
            ],
        );
    }
}
