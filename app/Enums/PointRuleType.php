<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PointRuleType: string implements HasLabel
{
    case Fixed = 'fixed';
    case Percentage = 'percentage';

    public function getLabel(): ?string
    {
        return __('enums.point_rule_type.'.$this->value);
    }
}
