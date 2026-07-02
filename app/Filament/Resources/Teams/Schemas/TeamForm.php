<?php

namespace App\Filament\Resources\Teams\Schemas;

use App\Models\Season;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TeamForm
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
                        TextInput::make('name')
                            ->label(__('admin.resources.teams.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon(Heroicon::OutlinedUserGroup),
                        TextInput::make('name_ar')
                            ->label(__('admin.resources.teams.fields.name_ar'))
                            ->maxLength(255)
                            ->hint(__('admin.common.arabic'))
                            ->extraInputAttributes(['dir' => 'rtl']),
                        TextInput::make('age_group')
                            ->label(__('admin.resources.teams.fields.age_group'))
                            ->required()
                            ->maxLength(255)
                            ->placeholder('U12'),
                        Select::make('season_id')
                            ->label(__('admin.common.season'))
                            ->options(fn (): array => Season::query()->orderByDesc('is_active')->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload(),
                    ]),
            ]);
    }
}
