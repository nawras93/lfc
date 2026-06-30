<?php

namespace App\Filament\Resources\Fixtures\Tables;

use App\Enums\FixtureStatus;
use App\Models\Fixture;
use App\Models\Team;
use App\Support\EnumOptions;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FixturesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kickoff_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('team.name')
                    ->label('Team')
                    ->sortable(),
                TextColumn::make('opponent')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('scan_opens_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('scan_closes_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(EnumOptions::for(FixtureStatus::class)),
                SelectFilter::make('team_id')
                    ->label('Team')
                    ->options(fn (): array => Team::query()->orderBy('name')->pluck('name', 'id')->all()),
            ])
            ->defaultSort('kickoff_at', 'desc')
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                FixturesTable::openForScanningAction(),
                FixturesTable::closeScanningAction(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function openForScanningAction(): Action
    {
        return Action::make('openForScanning')
            ->label('Open for Scanning')
            ->icon('heroicon-o-lock-open')
            ->color('success')
            ->requiresConfirmation()
            ->action(fn (Fixture $record) => $record->update(['status' => FixtureStatus::OpenForScanning]));
    }

    public static function closeScanningAction(): Action
    {
        return Action::make('closeScanning')
            ->label('Close Scanning')
            ->icon('heroicon-o-lock-closed')
            ->color('gray')
            ->requiresConfirmation()
            ->action(fn (Fixture $record) => $record->update(['status' => FixtureStatus::Closed]));
    }
}
