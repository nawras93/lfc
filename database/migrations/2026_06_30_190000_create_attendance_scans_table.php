<?php

use App\Models\Fixture;
use App\Models\ParentAccount;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ParentAccount::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Fixture::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'scanned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('scanned_at');
            $table->timestamps();

            $table->unique(['parent_account_id', 'fixture_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_scans');
    }
};
