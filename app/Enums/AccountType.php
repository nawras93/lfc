<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AccountType: string implements HasColor, HasLabel
{
    case Parent = 'parent';
    case VvipClient = 'vvip_client';
    case Member = 'member';
    case VvipMember = 'vvip_member';

    public function getLabel(): ?string
    {
        return __('enums.account_type.'.$this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Parent => 'info',
            self::VvipClient => 'warning',
            self::Member => 'success',
            self::VvipMember => 'warning',
        };
    }
}
