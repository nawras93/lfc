<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FederationStatus: string implements HasColor, HasLabel
{
    case NotStarted = 'not_started';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Returned = 'returned';

    public function getLabel(): ?string
    {
        return __('enums.federation_status.'.$this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NotStarted => 'gray',
            self::Submitted => 'info',
            self::Approved => 'success',
            self::Returned => 'danger',
        };
    }
}
