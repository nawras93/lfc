<?php

namespace App\Filament\Resources\Teams\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class TeamInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('age_group'),
                TextEntry::make('season.name')
                    ->label('Season'),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}
