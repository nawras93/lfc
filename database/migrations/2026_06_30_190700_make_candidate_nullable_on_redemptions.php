<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('redemptions', function (Blueprint $table) {
            $table->dropForeign(['candidate_id']);
            $table->foreignId('candidate_id')->nullable()->change();
            $table->foreign('candidate_id')->references('id')->on('candidates')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('redemptions', function (Blueprint $table) {
            $table->dropForeign(['candidate_id']);
            $table->foreignId('candidate_id')->nullable(false)->change();
            $table->foreign('candidate_id')->references('id')->on('candidates')->cascadeOnDelete();
        });
    }
};
