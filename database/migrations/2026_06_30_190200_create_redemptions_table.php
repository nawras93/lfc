<?php

use App\Models\Candidate;
use App\Models\ParentAccount;
use App\Models\RedemptionItem;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ParentAccount::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Candidate::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(RedemptionItem::class)->constrained()->cascadeOnDelete();
            $table->integer('points_spent');
            $table->string('voucher_code')->unique();
            $table->string('status'); // issued|fulfilled|cancelled
            $table->dateTime('fulfilled_at')->nullable();
            $table->foreignIdFor(User::class, 'fulfilled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redemptions');
    }
};
