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

    protected static ?string $title = null;

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('admin.resources.candidates.relations.points_ledger');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label(__('admin.common.date'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('admin.resources.point_rules.fields.type'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('points')
                    ->label(__('admin.common.points'))
                    ->formatStateUsing(fn (int $state): string => $state >= 0 ? "+{$state}" : (string) $state)
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger'),
                TextColumn::make('pointRule.name')
                    ->label(__('admin.common.rule'))
                    ->placeholder(__('admin.common.not_available')),
                TextColumn::make('reason')
                    ->label(__('admin.resources.candidates.fields.reason'))
                    ->placeholder(__('admin.common.not_available')),
                TextColumn::make('createdBy.name')
                    ->label(__('admin.common.by'))
                    ->placeholder(__('admin.common.not_available')),
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
