<?php

namespace App\Filament\Resources\Seasons\Schemas;

use App\Models\Season;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SeasonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Season Details')
                    ->description('Core season configuration used by recruitment and team setup.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Active season'),
                    ]),
                Section::make('Public Registration')
                    ->description('Generate and manage the season-specific public registration link shared with parents.')
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('registration_starts_at')
                            ->label('Registration opens at')
                            ->seconds(false),
                        DateTimePicker::make('registration_ends_at')
                            ->label('Registration closes at')
                            ->seconds(false)
                            ->after('registration_starts_at'),
                        TextInput::make('registration_slug')
                            ->label('Registration token')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn (?Season $record): ?string => $record?->registration_slug)
                            ->helperText('System-generated random prefix. The public link stays private unless you share it.'),
                        Placeholder::make('registration_status')
                            ->label('Current status')
                            ->content(function (?Season $record): string {
                                if ($record === null || $record->registration_starts_at === null || $record->registration_ends_at === null) {
                                    return 'Set both dates to activate a registration window.';
                                }

                                return $record->registrationIsOpen()
                                    ? 'Open now'
                                    : 'Closed';
                            }),
                        TextInput::make('public_registration_url')
                            ->label('Public registration URL')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->formatStateUsing(fn (?Season $record): string => $record?->publicRegistrationUrl() ?? 'Save the season to generate the public registration URL.')
                            ->helperText('Copy and share this exact URL with parents for this season.'),
                    ]),
            ]);
    }
}
