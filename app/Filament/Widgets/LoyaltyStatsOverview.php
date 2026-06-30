<?php

namespace App\Filament\Widgets;

use App\Services\LoyaltyMetrics;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LoyaltyStatsOverview extends StatsOverviewWidget
{
    protected function getColumns(): int | array
    {
        return 3;
    }

    protected function getStats(): array
    {
        $metrics = app(LoyaltyMetrics::class);

        return [
            Stat::make('Points Issued', $metrics->pointsIssued())
                ->icon(Heroicon::OutlinedArrowTrendingUp),

            Stat::make('Points Redeemed', $metrics->pointsRedeemed())
                ->icon(Heroicon::OutlinedArrowTrendingDown),

            Stat::make('Outstanding Liability', $metrics->outstandingLiability())
                ->description('Net points owed to players & VVIP clients')
                ->color('warning'),

            Stat::make('Attendance Scans', $metrics->attendanceScans())
                ->icon(Heroicon::OutlinedQrCode),

            Stat::make('Pending Fulfillments', $metrics->pendingFulfillments())
                ->icon(Heroicon::OutlinedClock),

            Stat::make('VVIP Clients', $metrics->vvipClients())
                ->icon(Heroicon::OutlinedStar),
        ];
    }
}
