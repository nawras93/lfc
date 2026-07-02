<?php

namespace App\Filament\Resources\Seasons\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class SeasonInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.seasons.sections.details'))
                    ->description(__('admin.resources.seasons.descriptions.details'))
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->iconColor('primary')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('admin.resources.seasons.fields.name'))
                            ->weight(FontWeight::Bold),
                        IconEntry::make('is_active')
                            ->label(__('admin.resources.seasons.fields.is_active'))
                            ->boolean(),
                        TextEntry::make('created_at')
                            ->label(__('admin.common.created_at'))
                            ->icon(Heroicon::OutlinedClock)
                            ->dateTime(),
                    ]),
                Section::make(__('admin.resources.seasons.sections.public_registration'))
                    ->description(__('admin.resources.seasons.descriptions.public_registration'))
                    ->icon(Heroicon::OutlinedLink)
                    ->iconColor('primary')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('registration_starts_at')
                            ->label(__('admin.resources.seasons.fields.registration_starts_at'))
                            ->icon(Heroicon::OutlinedCalendar)
                            ->dateTime()
                            ->placeholder(__('admin.common.not_available')),
                        TextEntry::make('registration_ends_at')
                            ->label(__('admin.resources.seasons.fields.registration_ends_at'))
                            ->icon(Heroicon::OutlinedCalendar)
                            ->dateTime()
                            ->placeholder(__('admin.common.not_available')),
                        IconEntry::make('registrationIsOpen')
                            ->label(__('admin.resources.seasons.fields.registration_is_open'))
                            ->state(fn ($record): bool => $record->registrationIsOpen())
                            ->boolean(),
                        TextEntry::make('registration_slug')
                            ->label(__('admin.resources.seasons.fields.registration_slug'))
                            ->placeholder(__('admin.common.not_available')),
                        TextEntry::make('public_registration_url')
                            ->label(__('admin.resources.seasons.fields.public_registration_url'))
                            ->state(fn ($record): ?string => $record->publicRegistrationUrl())
                            ->icon(Heroicon::OutlinedLink)
                            ->copyable()
                            ->copyableState(fn ($record): ?string => $record->publicRegistrationUrl())
                            ->placeholder(__('admin.resources.seasons.messages.save_to_generate_url'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
