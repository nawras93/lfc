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
            Stat::make(__('admin.widgets.loyalty_stats.points_issued'), $metrics->pointsIssued())
                ->icon(Heroicon::OutlinedArrowTrendingUp),

            Stat::make(__('admin.widgets.loyalty_stats.points_redeemed'), $metrics->pointsRedeemed())
                ->icon(Heroicon::OutlinedArrowTrendingDown),

            Stat::make(__('admin.widgets.loyalty_stats.outstanding_liability'), $metrics->outstandingLiability())
                ->description(__('admin.widgets.loyalty_stats.outstanding_liability_description'))
                ->color('warning'),

            Stat::make(__('admin.widgets.loyalty_stats.attendance_scans'), $metrics->attendanceScans())
                ->icon(Heroicon::OutlinedQrCode),

            Stat::make(__('admin.widgets.loyalty_stats.pending_fulfillments'), $metrics->pendingFulfillments())
                ->icon(Heroicon::OutlinedClock),

            Stat::make(__('admin.widgets.loyalty_stats.vvip_clients'), $metrics->vvipClients())
                ->icon(Heroicon::OutlinedStar),
        ];
    }
}
