<?php

namespace App\Filament\Widgets;

use App\Enums\RedemptionStatus;
use App\Filament\Resources\Redemptions\RedemptionResource;
use App\Models\Redemption;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingFulfillmentsTable extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = null;

    public static function getHeading(): ?string
    {
        return __('admin.widgets.pending_fulfillments.heading');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Redemption::where('status', RedemptionStatus::Issued)
            )
            ->columns([
                TextColumn::make('parent.name')
                    ->label(__('admin.common.account'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('player.full_name')
                    ->label(__('admin.common.player'))
                    ->formatStateUsing(fn (?string $state): string => $state ?? __('admin.common.not_available')),
                TextColumn::make('item.name')
                    ->label(__('admin.common.item'))
                    ->sortable(),
                TextColumn::make('points_spent')
                    ->label(__('admin.resources.redemptions.fields.points_spent'))
                    ->sortable(),
                TextColumn::make('voucher_code')
                    ->label(__('admin.resources.redemptions.fields.voucher_code'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('admin.common.voucher_code_copied')),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                RedemptionResource::markFulfilledAction(),
            ])
            ->emptyStateHeading(__('admin.resources.redemptions.messages.empty_state'))
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
