<?php

namespace App\Filament\Resources\Fixtures\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class FixtureInfolist
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
                        TextEntry::make('opponent')
                            ->label(__('admin.resources.fixtures.fields.opponent'))
                            ->weight(FontWeight::Bold)
                            ->icon(Heroicon::OutlinedShieldCheck)
                            ->columnSpanFull(),
                        TextEntry::make('team.name')
                            ->label(__('admin.common.team'))
                            ->icon(Heroicon::OutlinedUserGroup),
                        TextEntry::make('season.name')
                            ->label(__('admin.common.season'))
                            ->icon(Heroicon::OutlinedCalendar)
                            ->placeholder(__('admin.common.not_available')),
                        TextEntry::make('venue')
                            ->label(__('admin.resources.fixtures.fields.venue'))
                            ->icon(Heroicon::OutlinedMapPin),
                        TextEntry::make('kickoff_at')
                            ->label(__('admin.resources.fixtures.fields.kickoff_at'))
                            ->icon(Heroicon::OutlinedCalendarDays)
                            ->dateTime(),
                        TextEntry::make('status')
                            ->label(__('admin.common.status'))
                            ->badge(),
                    ]),
                Section::make(__('admin.resources.fixtures.sections.scanning'))
                    ->description(__('admin.resources.fixtures.descriptions.scanning'))
                    ->icon(Heroicon::OutlinedClock)
                    ->iconColor('primary')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('scan_opens_at')
                            ->label(__('admin.resources.fixtures.fields.scan_opens_at'))
                            ->icon(Heroicon::OutlinedClock)
                            ->dateTime()
                            ->placeholder(__('admin.common.not_available')),
                        TextEntry::make('scan_closes_at')
                            ->label(__('admin.resources.fixtures.fields.scan_closes_at'))
                            ->icon(Heroicon::OutlinedClock)
                            ->dateTime()
                            ->placeholder(__('admin.common.not_available')),
                    ]),
            ]);
    }
}
