<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JoiningStatus: string implements HasColor, HasLabel
{
    case NotStarted = 'not_started';
    case ReadyToJoin = 'ready_to_join';
    case JoinedTeam = 'joined_team';

    public function getLabel(): ?string
    {
        return __('enums.joining_status.'.$this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NotStarted => 'gray',
            self::ReadyToJoin => 'warning',
            self::JoinedTeam => 'success',
        };
    }
}
