<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RecruitmentStage: string implements HasLabel
{
    case NewApplication = 'new_application';
    case AssessmentScheduled = 'assessment_scheduled';
    case AssessmentCompleted = 'assessment_completed';
    case Accepted = 'accepted';
    case WaitingList = 'waiting_list';
    case Rejected = 'rejected';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::NewApplication => 'New Application',
            self::AssessmentScheduled => 'Assessment Scheduled',
            self::AssessmentCompleted => 'Assessment Completed',
            self::Accepted => 'Accepted',
            self::WaitingList => 'Waiting List',
            self::Rejected => 'Rejected',
        };
    }
}
