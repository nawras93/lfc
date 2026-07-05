<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fixtures', function (Blueprint $table): void {
            $table->dropForeign(['team_id']);
        });

        Schema::table('fixtures', function (Blueprint $table): void {
            $table->foreignId('team_id')->nullable()->change();
            $table->string('competition')->nullable()->after('opponent');
            $table->boolean('is_home')->default(true)->after('competition');
            $table->unsignedTinyInteger('our_score')->nullable()->after('status');
            $table->unsignedTinyInteger('opponent_score')->nullable()->after('our_score');
        });

        Schema::table('fixtures', function (Blueprint $table): void {
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fixtures', function (Blueprint $table): void {
            $table->dropForeign(['team_id']);
        });

        Schema::table('fixtures', function (Blueprint $table): void {
            $table->dropColumn(['competition', 'is_home', 'our_score', 'opponent_score']);
            $table->foreignId('team_id')->nullable(false)->change();
        });

        Schema::table('fixtures', function (Blueprint $table): void {
            $table->foreign('team_id')->references('id')->on('teams')->cascadeOnDelete();
        });
    }
};
