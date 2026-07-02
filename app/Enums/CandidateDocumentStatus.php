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
        return __('enums.candidate_document_status.'.$this->value);
    }
}
