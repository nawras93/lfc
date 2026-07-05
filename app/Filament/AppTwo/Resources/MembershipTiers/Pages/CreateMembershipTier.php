<?php

namespace App\Filament\AppTwo\Resources\MembershipTiers\Pages;

use App\Filament\AppTwo\Resources\MembershipTiers\MembershipTierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMembershipTier extends CreateRecord
{
    protected static string $resource = MembershipTierResource::class;
}
