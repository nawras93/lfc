<?php

use App\Enums\AppKey;
use App\Enums\LedgerUnit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parent_accounts', function (Blueprint $table) {
            $table->string('app')
                ->default(AppKey::AppOne->value)
                ->after('account_type')
                ->index();
        });

        Schema::table('fixtures', function (Blueprint $table) {
            $table->string('app')
                ->default(AppKey::AppOne->value)
                ->after('status')
                ->index();
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->string('app')
                ->default(AppKey::AppOne->value)
                ->after('audience')
                ->index();
        });

        Schema::table('point_transactions', function (Blueprint $table) {
            $table->string('unit', 20)
                ->default(LedgerUnit::Points->value)
                ->after('points');
        });
    }

    public function down(): void
    {
        Schema::table('point_transactions', function (Blueprint $table) {
            $table->dropColumn('unit');
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn('app');
        });

        Schema::table('fixtures', function (Blueprint $table) {
            $table->dropColumn('app');
        });

        Schema::table('parent_accounts', function (Blueprint $table) {
            $table->dropColumn('app');
        });
    }
};
