<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OfferAudience: string implements HasColor, HasLabel
{
    case All = 'all';
    case VVIP = 'vvip';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::All => 'All Parents',
            self::VVIP => 'VVIP Only',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::All => 'info',
            self::VVIP => 'warning',
        };
    }
}
