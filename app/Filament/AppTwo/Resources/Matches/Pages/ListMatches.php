<?php

namespace App\Filament\AppTwo\Resources\Matches\Pages;

use App\Filament\AppTwo\Resources\Matches\MatchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMatches extends ListRecords
{
    protected static string $resource = MatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
