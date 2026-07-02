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
        return __('enums.offer_audience.'.$this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::All => 'info',
            self::VVIP => 'warning',
        };
    }
}
