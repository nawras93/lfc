<?php

namespace App\Filament\AppTwo\Resources\MembershipTiers\Pages;

use App\Filament\AppTwo\Resources\MembershipTiers\MembershipTierResource;
use Filament\Resources\Pages\EditRecord;

class EditMembershipTier extends EditRecord
{
    protected static string $resource = MembershipTierResource::class;
}
