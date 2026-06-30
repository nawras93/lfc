<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_player_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['parent_account_id', 'candidate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_player_links');
    }
};
