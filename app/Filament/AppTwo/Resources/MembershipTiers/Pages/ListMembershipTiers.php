<?php

namespace App\Filament\AppTwo\Resources\MembershipTiers\Pages;

use App\Filament\AppTwo\Resources\MembershipTiers\MembershipTierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMembershipTiers extends ListRecords
{
    protected static string $resource = MembershipTierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
