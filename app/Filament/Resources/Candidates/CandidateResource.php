<?php

namespace App\Filament\Resources\Candidates;

use App\Enums\RecruitmentStage;
use App\Exceptions\InvalidRecruitmentStageTransition;
use App\Filament\Resources\Candidates\Pages\CreateCandidate;
use App\Filament\Resources\Candidates\Pages\EditCandidate;
use App\Filament\Resources\Candidates\Pages\ListCandidates;
use App\Filament\Resources\Candidates\Pages\ViewCandidate;
use App\Filament\Resources\Candidates\RelationManagers\CandidateDocumentsRelationManager;
use App\Filament\Resources\Candidates\Schemas\CandidateForm;
use App\Filament\Resources\Candidates\Schemas\CandidateInfolist;
use App\Filament\Resources\Candidates\Tables\CandidatesTable;
use App\Models\Candidate;
use App\Models\Team;
use App\Services\RecruitmentStageGuard;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|\UnitEnum|null $navigationGroup = 'Recruitment';

    public static function form(Schema $schema): Schema
    {
        return CandidateForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CandidateInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CandidatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            CandidateDocumentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCandidates::route('/'),
            'create' => CreateCandidate::route('/create'),
            'view' => ViewCandidate::route('/{record}'),
            'edit' => EditCandidate::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['full_name', 'parent_phone', 'recruitment_stage'];
    }

    /**
     * @return array<string, mixed>
     */
    public static function mutateCandidateData(array $data): array
    {
        $consentGiven = (bool) ($data['consent_given'] ?? false);

        if ($consentGiven) {
            $data['consent_at'] ??= now();
        } else {
            $data['consent_at'] = null;
        }

        return $data;
    }

    public static function makeChangeRecruitmentStageAction(): Action
    {
        return Action::make('changeRecruitmentStage')
            ->label('Change recruitment stage')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('warning')
            ->schema(CandidateForm::recruitmentStageActionSchema())
            ->fillForm(fn (Candidate $record): array => [
                'recruitment_stage' => $record->recruitment_stage->value,
            ])
            ->action(function (Candidate $record, array $data): void {
                try {
                    app(RecruitmentStageGuard::class)->transition(
                        $record,
                        RecruitmentStage::from($data['recruitment_stage']),
                        auth()->user(),
                        $data['note'] ?? null,
                    );
                } catch (InvalidRecruitmentStageTransition $exception) {
                    Notification::make()
                        ->danger()
                        ->title('Invalid recruitment stage transition')
                        ->body($exception->getMessage())
                        ->send();

                    throw (new Halt)->rollBackDatabaseTransaction();
                }
            });
    }

    public static function makeMarkAsPlayerAction(): Action
    {
        return Action::make('markAsPlayer')
            ->label('Mark as player')
            ->icon(Heroicon::OutlinedUserPlus)
            ->color('success')
            ->disabled(fn (Candidate $record): bool => ! $record->canBeMarkedAsPlayer())
            ->schema([
                Select::make('team_id')
                    ->label('Team')
                    ->options(fn (): array => Team::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->required(),
            ])
            ->fillForm(fn (Candidate $record): array => [
                'team_id' => $record->team_id,
            ])
            ->action(function (Candidate $record, array $data): void {
                if (! $record->canBeMarkedAsPlayer()) {
                    Notification::make()
                        ->danger()
                        ->title('Candidate is not eligible to become a player')
                        ->send();

                    throw (new Halt)->rollBackDatabaseTransaction();
                }

                $record->forceFill([
                    'team_id' => $data['team_id'],
                    'is_player' => true,
                ])->save();
            });
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->full_name;
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Parent phone' => $record->parent_phone,
            'Recruitment stage' => $record->recruitment_stage->getLabel(),
        ];
    }
}
