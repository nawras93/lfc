<?php

namespace App\Filament\Resources\Teams\Schemas;

use App\Models\Team;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class TeamInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.teams.sections.details'))
                    ->description(__('admin.resources.teams.descriptions.details'))
                    ->icon(Heroicon::OutlinedUserGroup)
                    ->iconColor('primary')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('admin.resources.teams.fields.name'))
                            ->state(fn (Team $record): ?string => $record->localized('name'))
                            ->weight(FontWeight::Bold)
                            ->icon(Heroicon::OutlinedUserGroup),
                        TextEntry::make('age_group')
                            ->label(__('admin.resources.teams.fields.age_group'))
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('season.name')
                            ->label(__('admin.common.season'))
                            ->icon(Heroicon::OutlinedCalendar)
                            ->placeholder(__('admin.common.not_available')),
                        TextEntry::make('created_at')
                            ->label(__('admin.common.created_at'))
                            ->icon(Heroicon::OutlinedClock)
                            ->dateTime(),
                    ]),
            ]);
    }
}
