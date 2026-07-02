<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Backfill a registration token for any existing season that is missing one.
     *
     * Seasons seeded via DatabaseSeeder (which runs WithoutModelEvents) never
     * fired the model hook that generates the token, so they were left with a
     * null slug — which makes publicRegistrationUrl() null and 500s the public
     * registration page. Give each such season a unique token now.
     */
    public function up(): void
    {
        $seasons = DB::table('seasons')
            ->where(function ($query): void {
                $query->whereNull('registration_slug')->orWhere('registration_slug', '');
            })
            ->pluck('id');

        foreach ($seasons as $id) {
            DB::table('seasons')
                ->where('id', $id)
                ->update(['registration_slug' => Str::lower(Str::random(16))]);
        }
    }

    public function down(): void
    {
        // No-op: tokens are not rolled back (removing them would break shared links).
    }
};
