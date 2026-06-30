<?php

namespace App\Filament\Resources\Candidates\Pages;

use App\Enums\RecruitmentStage;
use App\Exceptions\InvalidRecruitmentStageTransition;
use App\Filament\Resources\Candidates\CandidateResource;
use App\Models\Candidate;
use App\Services\RecruitmentStageGuard;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class EditCandidate extends EditRecord
{
    protected static string $resource = CandidateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CandidateResource::makeChangeRecruitmentStageAction(),
            CandidateResource::makeMarkAsPlayerAction(),
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return CandidateResource::mutateCandidateData($data);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Candidate $record */
        $targetStage = RecruitmentStage::from($data['recruitment_stage']);
        unset($data['recruitment_stage']);

        $record->update($data);

        if ($record->recruitment_stage === $targetStage) {
            return $record;
        }

        try {
            app(RecruitmentStageGuard::class)->transition($record, $targetStage, auth()->user());
        } catch (InvalidRecruitmentStageTransition $exception) {
            Notification::make()
                ->danger()
                ->title('Invalid recruitment stage transition')
                ->body($exception->getMessage())
                ->send();

            throw (new Halt)->rollBackDatabaseTransaction();
        }

        return $record->refresh();
    }
}
