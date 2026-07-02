<?php

namespace App\Filament\Resources\Teams\Tables;

use App\Models\Team;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.resources.teams.fields.name'))
                    ->state(fn (Team $record): ?string => $record->localized('name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('age_group')
                    ->label(__('admin.resources.teams.fields.age_group'))
                    ->sortable(),
                TextColumn::make('season.name')
                    ->label(__('admin.common.season'))
                    ->sortable(),
                TextColumn::make('candidates_count')
                    ->counts('candidates')
                    ->label(__('admin.resources.teams.fields.candidates_count')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
