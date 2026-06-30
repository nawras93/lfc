<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // earn|redeem|expire|adjust|reverse
            $table->integer('points');
            $table->foreignId('point_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('source');
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('candidate_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('point_transactions');
    }
};
