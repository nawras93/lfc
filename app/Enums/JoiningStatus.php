<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum JoiningStatus: string implements HasLabel
{
    case NotStarted = 'not_started';
    case ReadyToJoin = 'ready_to_join';
    case JoinedTeam = 'joined_team';

    public function getLabel(): ?string
    {
        return __('enums.joining_status.'.$this->value);
    }
}
