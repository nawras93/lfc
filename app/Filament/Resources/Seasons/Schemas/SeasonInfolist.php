<?php

namespace App\Filament\Resources\Seasons\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SeasonInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('admin.resources.seasons.fields.name')),
                IconEntry::make('is_active')
                    ->label(__('admin.resources.seasons.fields.is_active'))
                    ->boolean(),
                TextEntry::make('registration_starts_at')
                    ->label(__('admin.resources.seasons.fields.registration_starts_at'))
                    ->dateTime(),
                TextEntry::make('registration_ends_at')
                    ->label(__('admin.resources.seasons.fields.registration_ends_at'))
                    ->dateTime(),
                TextEntry::make('registration_slug')
                    ->label(__('admin.resources.seasons.fields.registration_slug')),
                TextEntry::make('public_registration_url')
                    ->label(__('admin.resources.seasons.fields.public_registration_url'))
                    ->state(fn ($record): ?string => $record->publicRegistrationUrl())
                    ->copyable()
                    ->copyableState(fn ($record): ?string => $record->publicRegistrationUrl())
                    ->placeholder(__('admin.resources.seasons.messages.save_to_generate_url'))
                    ->columnSpanFull(),
                IconEntry::make('registrationIsOpen')
                    ->label(__('admin.resources.seasons.fields.registration_is_open'))
                    ->state(fn ($record): bool => $record->registrationIsOpen())
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime(),
            ]);
    }
}
