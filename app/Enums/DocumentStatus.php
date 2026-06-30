<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DocumentStatus: string implements HasLabel
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Complete = 'complete';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProgress => 'In Progress',
            self::Complete => 'Complete',
        };
    }
}
