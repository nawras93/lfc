<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixtures', function (Blueprint $table): void {
            $table->string('opponent_ar')->nullable()->after('opponent');
            $table->string('competition_ar')->nullable()->after('competition');
        });
    }

    public function down(): void
    {
        Schema::table('fixtures', function (Blueprint $table): void {
            $table->dropColumn(['opponent_ar', 'competition_ar']);
        });
    }
};
