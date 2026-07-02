<?php

namespace App\Filament\Resources\Fixtures\Schemas;

use App\Enums\FixtureStatus;
use App\Models\Season;
use App\Models\Team;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class FixtureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('team_id')
                    ->label(__('admin.common.team'))
                    ->options(fn (): array => Team::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('season_id')
                    ->label(__('admin.common.season'))
                    ->options(fn (): array => Season::query()->orderByDesc('is_active')->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),
                TextInput::make('opponent')
                    ->label(__('admin.resources.fixtures.fields.opponent'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('venue')
                    ->label(__('admin.resources.fixtures.fields.venue'))
                    ->required()
                    ->maxLength(255),
                DateTimePicker::make('kickoff_at')
                    ->label(__('admin.resources.fixtures.fields.kickoff_at'))
                    ->required(),
                DateTimePicker::make('scan_opens_at')
                    ->label(__('admin.resources.fixtures.fields.scan_opens_at')),
                DateTimePicker::make('scan_closes_at')
                    ->label(__('admin.resources.fixtures.fields.scan_closes_at')),
                Select::make('status')
                    ->label(__('admin.common.status'))
                    ->options(FixtureStatus::class)
                    ->required(),
            ]);
    }
}
