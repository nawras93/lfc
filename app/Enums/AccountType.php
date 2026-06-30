<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AccountType: string implements HasColor, HasLabel
{
    case Parent = 'parent';
    case VvipClient = 'vvip_client';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Parent => 'Parent',
            self::VvipClient => 'VVIP Client',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Parent => 'info',
            self::VvipClient => 'warning',
        };
    }
}
