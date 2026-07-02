<?php

namespace App\Filament\Resources\Seasons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeasonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.resources.seasons.fields.name'))
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('admin.resources.seasons.fields.is_active'))
                    ->boolean()
                    ->sortable(),
                IconColumn::make('registrationIsOpen')
                    ->label(__('admin.resources.seasons.fields.registration_is_open'))
                    ->state(fn ($record): bool => $record->registrationIsOpen())
                    ->boolean(),
                TextColumn::make('teams_count')
                    ->counts('teams')
                    ->label(__('admin.resources.seasons.fields.teams_count')),
                TextColumn::make('registration_ends_at')
                    ->label(__('admin.resources.seasons.fields.registration_closes'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('public_registration_url')
                    ->label(__('admin.resources.seasons.fields.public_registration_url'))
                    ->state(fn ($record): ?string => $record->publicRegistrationUrl())
                    ->copyable()
                    ->copyableState(fn ($record): ?string => $record->publicRegistrationUrl())
                    ->limit(28)
                    ->tooltip(fn ($record): ?string => $record->publicRegistrationUrl())
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label(__('admin.common.updated_at'))
                    ->since()
                    ->sortable(),
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
