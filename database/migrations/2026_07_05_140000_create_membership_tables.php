<?php

use App\Enums\AppKey;
use App\Models\MembershipTier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_tiers', function (Blueprint $table): void {
            $table->id();
            $table->string('app')->default(AppKey::AppOne->value)->index();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->unsignedInteger('level');
            $table->string('accent_color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('membership_benefits', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(MembershipTier::class)->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('parent_accounts', function (Blueprint $table): void {
            $table->foreignIdFor(MembershipTier::class)
                ->nullable()
                ->after('account_type')
                ->constrained()
                ->nullOnDelete();
            $table->string('member_number')->nullable()->after('membership_tier_id');
            $table->date('membership_valid_until')->nullable()->after('member_number');
        });
    }

    public function down(): void
    {
        Schema::table('parent_accounts', function (Blueprint $table): void {
            $table->dropConstrainedForeignIdFor(MembershipTier::class);
            $table->dropColumn(['member_number', 'membership_valid_until']);
        });

        Schema::dropIfExists('membership_benefits');
        Schema::dropIfExists('membership_tiers');
    }
};
