<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum RecruitmentStage: string implements HasColor, HasLabel
{
    case NewApplication = 'new_application';
    case AssessmentScheduled = 'assessment_scheduled';
    case AssessmentCompleted = 'assessment_completed';
    case Accepted = 'accepted';
    case WaitingList = 'waiting_list';
    case Rejected = 'rejected';

    public function getLabel(): ?string
    {
        return __('enums.recruitment_stage.'.$this->value);
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NewApplication => 'gray',
            self::AssessmentScheduled, self::AssessmentCompleted => 'info',
            self::Accepted => 'success',
            self::WaitingList => 'warning',
            self::Rejected => 'danger',
        };
    }
}
