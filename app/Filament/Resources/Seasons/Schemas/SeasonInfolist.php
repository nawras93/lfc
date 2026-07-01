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
                TextEntry::make('name'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('registration_starts_at')
                    ->dateTime(),
                TextEntry::make('registration_ends_at')
                    ->dateTime(),
                TextEntry::make('registration_slug')
                    ->label('Registration token'),
                TextEntry::make('public_registration_url')
                    ->label('Public registration URL')
                    ->state(fn ($record): ?string => $record->publicRegistrationUrl())
                    ->copyable()
                    ->copyableState(fn ($record): ?string => $record->publicRegistrationUrl())
                    ->placeholder('Save the season to generate the public registration URL.')
                    ->columnSpanFull(),
                IconEntry::make('registrationIsOpen')
                    ->label('Registration open')
                    ->state(fn ($record): bool => $record->registrationIsOpen())
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}
