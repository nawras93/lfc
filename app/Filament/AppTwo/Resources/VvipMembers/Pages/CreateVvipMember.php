<?php

namespace App\Filament\AppTwo\Resources\VvipMembers\Pages;

use App\Enums\AccountType;
use App\Filament\AppTwo\Resources\VvipMembers\VvipMemberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVvipMember extends CreateRecord
{
    protected static string $resource = VvipMemberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            ...$data,
            'account_type' => AccountType::VvipMember,
            'is_vvip' => true,
            'accepted_at' => now(),
        ];
    }
}
