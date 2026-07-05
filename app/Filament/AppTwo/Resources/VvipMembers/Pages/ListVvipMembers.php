<?php

namespace App\Filament\AppTwo\Resources\VvipMembers\Pages;

use App\Filament\AppTwo\Resources\VvipMembers\VvipMemberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVvipMembers extends ListRecords
{
    protected static string $resource = VvipMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
