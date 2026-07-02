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
                Section::make(__('admin.resources.seasons.sections.details'))
                    ->description(__('admin.resources.seasons.descriptions.details'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('admin.resources.seasons.fields.name'))
                            ->required()
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label(__('admin.resources.seasons.fields.is_active')),
                    ]),
                Section::make(__('admin.resources.seasons.sections.public_registration'))
                    ->description(__('admin.resources.seasons.descriptions.public_registration'))
                    ->columns(2)
                    ->schema([
                        DateTimePicker::make('registration_starts_at')
                            ->label(__('admin.resources.seasons.fields.registration_starts_at'))
                            ->seconds(false),
                        DateTimePicker::make('registration_ends_at')
                            ->label(__('admin.resources.seasons.fields.registration_ends_at'))
                            ->seconds(false)
                            ->after('registration_starts_at'),
                        TextInput::make('registration_slug')
                            ->label(__('admin.resources.seasons.fields.registration_slug'))
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn (?Season $record): ?string => $record?->registration_slug)
                            ->helperText(__('admin.resources.seasons.helper.registration_slug')),
                        Placeholder::make('registration_status')
                            ->label(__('admin.resources.seasons.fields.registration_status'))
                            ->content(function (?Season $record): string {
                                if ($record === null || $record->registration_starts_at === null || $record->registration_ends_at === null) {
                                    return __('admin.resources.seasons.messages.set_dates');
                                }

                                return $record->registrationIsOpen()
                                    ? __('admin.resources.seasons.messages.open_now')
                                    : __('admin.resources.seasons.messages.closed');
                            }),
                        TextInput::make('public_registration_url')
                            ->label(__('admin.resources.seasons.fields.public_registration_url'))
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull()
                            ->formatStateUsing(fn (?Season $record): string => $record?->publicRegistrationUrl() ?? __('admin.resources.seasons.messages.save_to_generate_url'))
                            ->helperText(__('admin.resources.seasons.helper.public_registration_url')),
                    ]),
            ]);
    }
}
