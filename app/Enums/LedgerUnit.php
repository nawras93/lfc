<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum LedgerUnit: string implements HasLabel
{
    case Points = 'points';
    case DiscountPct = 'discount_pct';

    public function getLabel(): ?string
    {
        return __('enums.ledger_unit.'.$this->value);
    }
}
