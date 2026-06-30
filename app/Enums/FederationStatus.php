<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FederationStatus: string implements HasLabel
{
    case NotStarted = 'not_started';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Returned = 'returned';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NotStarted => 'Not Started',
            self::Submitted => 'Submitted',
            self::Approved => 'Approved',
            self::Returned => 'Returned',
        };
    }
}
