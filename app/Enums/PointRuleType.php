<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PointRuleType: string implements HasColor, HasLabel
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';

    public function getLabel(): ?string
    {
        return __('enums.point_rule_type.'.$this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Fixed => 'info',
            self::Percentage => 'warning',
        };
    }
}
