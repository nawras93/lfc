<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seasons', function (Blueprint $table): void {
            $table->string('registration_slug')->nullable()->unique()->after('is_active');
            $table->timestamp('registration_starts_at')->nullable()->after('registration_slug');
            $table->timestamp('registration_ends_at')->nullable()->after('registration_starts_at');
        });

        DB::table('seasons')
            ->whereNull('registration_slug')
            ->orderBy('id')
            ->eachById(function (object $season): void {
                DB::table('seasons')
                    ->where('id', $season->id)
                    ->update([
                        'registration_slug' => Str::lower(Str::random(16)),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table): void {
            $table->dropUnique(['registration_slug']);
            $table->dropColumn([
                'registration_slug',
                'registration_starts_at',
                'registration_ends_at',
            ]);
        });
    }
};
