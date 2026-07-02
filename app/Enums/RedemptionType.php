<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RedemptionType: string implements HasColor, HasLabel
{
    case Fee = 'fee';
    case Event = 'event';
    case Merch = 'merch';

    public function getLabel(): ?string
    {
        return __('enums.redemption_type.'.$this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Fee => 'danger',
            self::Event => 'warning',
            self::Merch => 'success',
        };
    }
}
