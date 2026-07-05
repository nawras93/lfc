<?php

namespace App\Filament\AppTwo\Resources\Members\Pages;

use App\Filament\AppTwo\Resources\Members\MemberResource;
use Filament\Resources\Pages\ListRecords;

class ListMembers extends ListRecords
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
