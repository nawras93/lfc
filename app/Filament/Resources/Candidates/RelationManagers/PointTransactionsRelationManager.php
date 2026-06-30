<?php

namespace App\Filament\Resources\Candidates\RelationManagers;

use App\Enums\PointTransactionType;
use App\Support\EnumOptions;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PointTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'pointTransactions';

    protected static ?string $title = 'Points Ledger';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge()
                    ->sortable(),
                TextColumn::make('points')
                    ->formatStateUsing(fn (int $state): string => $state >= 0 ? "+{$state}" : (string) $state)
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger'),
                TextColumn::make('pointRule.name')
                    ->label('Rule')
                    ->placeholder('-'),
                TextColumn::make('reason')
                    ->placeholder('-'),
                TextColumn::make('createdBy.name')
                    ->label('By')
                    ->placeholder('-'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->options(EnumOptions::for(PointTransactionType::class)),
            ]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
