<?php

namespace App\Filament\Resources\Fixtures\Schemas;

use App\Enums\FixtureStatus;
use App\Models\Season;
use App\Models\Team;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class FixtureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.fixtures.sections.match'))
                    ->description(__('admin.resources.fixtures.descriptions.match'))
                    ->icon(Heroicon::OutlinedTrophy)
                    ->iconColor('primary')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
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
                            ->maxLength(255)
                            ->prefixIcon(Heroicon::OutlinedShieldCheck),
                        TextInput::make('venue')
                            ->label(__('admin.resources.fixtures.fields.venue'))
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon(Heroicon::OutlinedMapPin),
                        DateTimePicker::make('kickoff_at')
                            ->label(__('admin.resources.fixtures.fields.kickoff_at'))
                            ->required()
                            ->prefixIcon(Heroicon::OutlinedCalendarDays),
                        Select::make('status')
                            ->label(__('admin.common.status'))
                            ->options(FixtureStatus::class)
                            ->required()
                            ->native(false),
                    ]),
                Section::make(__('admin.resources.fixtures.sections.scanning'))
                    ->description(__('admin.resources.fixtures.descriptions.scanning'))
                    ->icon(Heroicon::OutlinedClock)
                    ->iconColor('primary')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('scan_opens_at')
                            ->label(__('admin.resources.fixtures.fields.scan_opens_at'))
                            ->prefixIcon(Heroicon::OutlinedClock),
                        DateTimePicker::make('scan_closes_at')
                            ->label(__('admin.resources.fixtures.fields.scan_closes_at'))
                            ->prefixIcon(Heroicon::OutlinedClock)
                            ->after('scan_opens_at'),
                    ]),
            ]);
    }
}
