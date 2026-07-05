<?php

namespace App\Filament\AppTwo\Resources\Standings\Pages;

use App\Filament\AppTwo\Resources\Standings\StandingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStandings extends ListRecords
{
    protected static string $resource = StandingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
