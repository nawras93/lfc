<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum PlayingPosition: string implements HasLabel
{
    case Goalkeeper = 'goalkeeper';
    case Defender = 'defender';
    case Midfielder = 'midfielder';
    case Attacker = 'attacker';

    public function getLabel(): ?string
    {
        return __('enums.playing_position.'.$this->value);
    }
}
