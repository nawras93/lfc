<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->unsignedSmallInteger('year_of_birth');
            $table->date('date_of_birth');
            $table->string('country_of_birth');
            $table->string('citizenship');
            $table->unsignedSmallInteger('year_arrived_qatar');
            $table->string('playing_position');
            $table->string('school');
            $table->string('previous_club');
            $table->string('parent_name');
            $table->string('parent_phone');
            $table->string('parent_whatsapp');
            $table->string('email')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('season_id')->constrained();
            $table->foreignId('team_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_player')->default(false);
            $table->boolean('consent_given')->default(false);
            $table->timestamp('consent_at')->nullable();
            $table->string('recruitment_stage')->default('new_application');
            $table->string('document_status')->default('pending');
            $table->string('qfa_status')->default('not_started');
            $table->string('fifa_status')->default('not_started');
            $table->string('joining_status')->default('not_started');
            $table->timestamp('status_updated_at')->nullable();
            $table->foreignId('status_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
