<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PlayingPosition: string implements HasColor, HasLabel
{
    case Goalkeeper = 'goalkeeper';
    case Defender = 'defender';
    case Midfielder = 'midfielder';
    case Attacker = 'attacker';

    public function getLabel(): ?string
    {
        return __('enums.playing_position.'.$this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Goalkeeper => 'warning',
            self::Defender => 'info',
            self::Midfielder => 'success',
            self::Attacker => 'danger',
        };
    }
}
