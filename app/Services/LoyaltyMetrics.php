<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class LoyaltyMetrics
{
    public function pointsIssued(): int
    {
        return (int) DB::table('point_transactions')
            ->where('points', '>', 0)
            ->sum('points');
    }

    public function pointsRedeemed(): int
    {
        return (int) DB::table('point_transactions')
            ->where('type', 'redeem')
            ->sum('points') * -1;
    }

    public function outstandingLiability(): int
    {
        return (int) DB::table('point_transactions')
            ->sum('points');
    }

    public function attendanceScans(): int
    {
        return DB::table('attendance_scans')->count();
    }

    public function pendingFulfillments(): int
    {
        return DB::table('redemptions')
            ->where('status', 'issued')
            ->count();
    }

    public function vvipClients(): int
    {
        return DB::table('parent_accounts')
            ->where('is_vvip', true)
            ->count();
    }
}
