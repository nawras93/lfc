<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AppKey: string implements HasLabel
{
    case AppOne = 'app_one';
    case AppTwo = 'app_two';

    public function getLabel(): ?string
    {
        return __('enums.app_keys.'.$this->value);
    }
}
