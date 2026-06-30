<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CandidateDocumentStatus: string implements HasLabel
{
    case Pending = 'pending';
    case Received = 'received';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Received => 'Received',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }
}
