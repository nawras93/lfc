<?php

namespace Database\Seeders;

use App\Models\Season;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SeasonSeeder extends Seeder
{
    public function run(): void
    {
        // Omit registration_slug from the payload so re-seeds don't rotate (and
        // break) the shared public registration link.
        $season = Season::query()->updateOrCreate(
            ['name' => '2026/27'],
            [
                'is_active' => true,
                'registration_starts_at' => now()->subWeek(),
                'registration_ends_at' => now()->addMonths(2),
            ],
        );

        // DatabaseSeeder runs WithoutModelEvents, which mutes the Season::creating
        // hook that normally generates the slug — so a plain `migrate:fresh --seed`
        // would leave it null and 500 the public registration page. Set it here,
        // guarded by blank() so it's assigned once and stays stable across re-seeds.
        if (blank($season->registration_slug)) {
            $season->registration_slug = Str::lower(Str::random(16));
            $season->saveQuietly();
        }
    }
}
