<?php

namespace App\Services;

use App\Enums\RecruitmentStage;
use App\Exceptions\InvalidRecruitmentStageTransition;
use App\Models\Candidate;
use App\Models\CandidateStatusHistory;
use App\Models\User;
use Illuminate\Support\Carbon;

class RecruitmentStageGuard
{
    /**
     * @return array<RecruitmentStage>
     */
    public function allowedTransitions(RecruitmentStage $from): array
    {
        return match ($from) {
            RecruitmentStage::NewApplication => [
                RecruitmentStage::AssessmentScheduled,
                RecruitmentStage::WaitingList,
                RecruitmentStage::Rejected,
            ],
            RecruitmentStage::AssessmentScheduled => [
                RecruitmentStage::AssessmentCompleted,
                RecruitmentStage::Rejected,
            ],
            RecruitmentStage::AssessmentCompleted => [
                RecruitmentStage::Accepted,
                RecruitmentStage::WaitingList,
                RecruitmentStage::Rejected,
            ],
            RecruitmentStage::WaitingList => [
                RecruitmentStage::Accepted,
                RecruitmentStage::Rejected,
            ],
            RecruitmentStage::Accepted,
            RecruitmentStage::Rejected => [],
        };
    }

    public function assertTransition(RecruitmentStage $from, RecruitmentStage $to): void
    {
        if ($from === $to) {
            return;
        }

        if (! in_array($to, $this->allowedTransitions($from), true)) {
            throw new InvalidRecruitmentStageTransition("Cannot move recruitment stage from [{$from->value}] to [{$to->value}].");
        }
    }

    public function transition(Candidate $candidate, RecruitmentStage $to, ?User $actor = null, ?string $note = null): Candidate
    {
        $from = $candidate->recruitment_stage;

        if ($from === $to) {
            return $candidate;
        }

        $this->assertTransition($from, $to);

        $changedAt = Carbon::now();

        $candidate->forceFill([
            'recruitment_stage' => $to,
            'status_updated_at' => $changedAt,
            'status_updated_by' => $actor?->getKey(),
        ])->save();

        CandidateStatusHistory::query()->create([
            'candidate_id' => $candidate->getKey(),
            'dimension' => 'recruitment_stage',
            'from_value' => $from->value,
            'to_value' => $to->value,
            'note' => $note,
            'changed_by' => $actor?->getKey(),
            'changed_at' => $changedAt,
        ]);

        return $candidate->refresh();
    }
}
