<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('point_transactions', function (Blueprint $table) {
            $table->foreignId('parent_account_id')
                ->nullable()
                ->after('candidate_id')
                ->constrained('parent_accounts')
                ->cascadeOnDelete();

            $table->index('parent_account_id');
        });

        Schema::table('point_transactions', function (Blueprint $table) {
            $table->dropForeign(['candidate_id']);
            $table->foreignId('candidate_id')->nullable()->change();
            $table->foreign('candidate_id')->references('id')->on('candidates')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('point_transactions', function (Blueprint $table) {
            $table->dropForeign(['parent_account_id']);
            $table->dropIndex(['parent_account_id']);
            $table->dropColumn('parent_account_id');
        });

        Schema::table('point_transactions', function (Blueprint $table) {
            $table->dropForeign(['candidate_id']);
            $table->foreignId('candidate_id')->nullable(false)->change();
            $table->foreign('candidate_id')->references('id')->on('candidates')->cascadeOnDelete();
        });
    }
};
