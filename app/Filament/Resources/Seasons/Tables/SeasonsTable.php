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
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                IconColumn::make('registrationIsOpen')
                    ->label('Registration open')
                    ->state(fn ($record): bool => $record->registrationIsOpen())
                    ->boolean(),
                TextColumn::make('teams_count')
                    ->counts('teams')
                    ->label('Teams'),
                TextColumn::make('registration_ends_at')
                    ->label('Registration closes')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('public_registration_url')
                    ->label('Public registration URL')
                    ->state(fn ($record): ?string => $record->publicRegistrationUrl())
                    ->copyable()
                    ->copyableState(fn ($record): ?string => $record->publicRegistrationUrl())
                    ->limit(28)
                    ->tooltip(fn ($record): ?string => $record->publicRegistrationUrl())
                    ->toggleable(),
                TextColumn::make('updated_at')
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
