<?php

namespace App\Filament\Resources\Teams\Schemas;

use App\Models\Team;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TeamInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('admin.resources.teams.fields.name'))
                    ->state(fn (Team $record): ?string => $record->localized('name')),
                TextEntry::make('age_group')
                    ->label(__('admin.resources.teams.fields.age_group')),
                TextEntry::make('season.name')
                    ->label(__('admin.common.season')),
                TextEntry::make('created_at')
                    ->label(__('admin.common.created_at'))
                    ->dateTime(),
            ]);
    }
}
