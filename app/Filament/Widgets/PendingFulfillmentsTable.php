<?php

namespace App\Filament\Widgets;

use App\Models\Redemption;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class PendingFulfillmentsTable extends TableWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Redemption::where('status', 'issued')
            )
            ->columns([
                TextColumn::make('parent.name')
                    ->label('Account')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('player.full_name')
                    ->label('Player')
                    ->formatStateUsing(fn (?string $state): string => $state ?? '—'),
                TextColumn::make('item.name')
                    ->label('Item')
                    ->sortable(),
                TextColumn::make('points_spent')
                    ->sortable(),
                TextColumn::make('voucher_code')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Voucher code copied'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
