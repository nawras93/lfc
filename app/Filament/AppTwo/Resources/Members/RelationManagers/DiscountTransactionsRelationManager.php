<?php

namespace App\Filament\AppTwo\Resources\Members\RelationManagers;

use App\Enums\LedgerUnit;
use App\Models\AttendanceScan;
use App\Models\PointTransaction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DiscountTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'pointTransactions';

    protected static ?string $title = null;

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('admin.resources.members.relations.discount_ledger');
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->where('unit', LedgerUnit::DiscountPct->value)
                ->with('source.fixture'))
            ->columns([
                TextColumn::make('source_fixture')
                    ->label(__('admin.common.fixture'))
                    ->state(function (PointTransaction $record): string {
                        $source = $record->source;

                        if (! $source instanceof AttendanceScan) {
                            return __('admin.common.not_available');
                        }

                        return $source->fixture?->opponent ?? __('admin.common.not_available');
                    }),
                TextColumn::make('points')
                    ->label(__('admin.resources.members.fields.discount_percent'))
                    ->formatStateUsing(fn (int $state): string => sprintf('+%.1f%%', $state / 100))
                    ->color('success'),
                TextColumn::make('created_at')
                    ->label(__('admin.common.date'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
