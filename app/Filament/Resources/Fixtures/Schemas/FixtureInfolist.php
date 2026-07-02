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
                    ->label(__('admin.common.team')),
                TextEntry::make('season.name')
                    ->label(__('admin.common.season')),
                TextEntry::make('opponent')
                    ->label(__('admin.resources.fixtures.fields.opponent')),
                TextEntry::make('venue')
                    ->label(__('admin.resources.fixtures.fields.venue')),
                TextEntry::make('kickoff_at')
                    ->label(__('admin.resources.fixtures.fields.kickoff_at'))
                    ->dateTime(),
                TextEntry::make('scan_opens_at')
                    ->label(__('admin.resources.fixtures.fields.scan_opens_at'))
                    ->dateTime(),
                TextEntry::make('scan_closes_at')
                    ->label(__('admin.resources.fixtures.fields.scan_closes_at'))
                    ->dateTime(),
                TextEntry::make('status')
                    ->label(__('admin.common.status'))
                    ->badge(),
            ]);
    }
}
