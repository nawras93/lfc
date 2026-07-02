<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum CandidateDocumentStatus: string implements HasColor, HasLabel
{
    case Pending = 'pending';
    case Received = 'received';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function getLabel(): ?string
    {
        return __('enums.candidate_document_status.'.$this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pending => 'gray',
            self::Received => 'info',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }
}
