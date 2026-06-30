<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FixtureStatus: string implements HasColor, HasLabel
{
    case Scheduled = 'scheduled';
    case OpenForScanning = 'open_for_scanning';
    case Closed = 'closed';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Scheduled => 'Scheduled',
            self::OpenForScanning => 'Open for Scanning',
            self::Closed => 'Closed',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Scheduled => 'info',
            self::OpenForScanning => 'success',
            self::Closed => 'gray',
        };
    }
}
