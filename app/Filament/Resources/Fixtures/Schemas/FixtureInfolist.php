<?php

namespace App\Filament\Resources\Fixtures\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FixtureInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('team.name')
                    ->label('Team'),
                TextEntry::make('season.name')
                    ->label('Season'),
                TextEntry::make('opponent'),
                TextEntry::make('venue'),
                TextEntry::make('kickoff_at')
                    ->dateTime(),
                TextEntry::make('scan_opens_at')
                    ->dateTime(),
                TextEntry::make('scan_closes_at')
                    ->dateTime(),
                TextEntry::make('status')
                    ->badge(),
            ]);
    }
}
