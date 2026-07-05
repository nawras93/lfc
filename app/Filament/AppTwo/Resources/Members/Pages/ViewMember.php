<?php

namespace App\Filament\AppTwo\Resources\Members\Pages;

use App\Filament\AppTwo\Resources\Members\MemberResource;
use Filament\Resources\Pages\ViewRecord;

class ViewMember extends ViewRecord
{
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
