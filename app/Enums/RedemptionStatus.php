<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RedemptionStatus: string implements HasColor, HasLabel
{
    case Issued = 'issued';
    case Fulfilled = 'fulfilled';
    case Cancelled = 'cancelled';

    public function getLabel(): ?string
    {
        return __('enums.redemption_status.'.$this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Issued => 'warning',
            self::Fulfilled => 'success',
            self::Cancelled => 'gray',
        };
    }
}
