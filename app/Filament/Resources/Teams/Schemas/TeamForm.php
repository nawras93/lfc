<?php

namespace App\Filament\Resources\Teams\Schemas;

use App\Models\Season;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin.resources.teams.fields.name'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('age_group')
                    ->label(__('admin.resources.teams.fields.age_group'))
                    ->required()
                    ->maxLength(255),
                Select::make('season_id')
                    ->label(__('admin.common.season'))
                    ->options(fn (): array => Season::query()->orderByDesc('is_active')->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),
            ]);
    }
}
