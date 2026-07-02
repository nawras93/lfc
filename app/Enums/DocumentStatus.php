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
        return __('enums.document_status.'.$this->value);
    }
}
