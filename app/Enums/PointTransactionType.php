<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PointTransactionType: string implements HasColor, HasLabel
{
    case Earn = 'earn';
    case Redeem = 'redeem';
    case Expire = 'expire';
    case Adjust = 'adjust';
    case Reverse = 'reverse';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Earn => 'Earn',
            self::Redeem => 'Redeem',
            self::Expire => 'Expire',
            self::Adjust => 'Adjust',
            self::Reverse => 'Reverse',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Earn => 'success',
            self::Redeem => 'warning',
            self::Expire => 'gray',
            self::Adjust => 'info',
            self::Reverse => 'danger',
        };
    }
}
