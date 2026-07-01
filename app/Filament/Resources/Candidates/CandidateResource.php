<?php

namespace App\Filament\Resources\Candidates;

use App\Enums\RecruitmentStage;
use App\Exceptions\InvalidRecruitmentStageTransition;
use App\Filament\Resources\Candidates\Pages\CreateCandidate;
use App\Filament\Resources\Candidates\Pages\EditCandidate;
use App\Filament\Resources\Candidates\Pages\ListCandidates;
use App\Filament\Resources\Candidates\Pages\ViewCandidate;
use App\Filament\Resources\Candidates\RelationManagers\CandidateDocumentsRelationManager;
use App\Filament\Resources\Candidates\RelationManagers\PointTransactionsRelationManager;
use App\Filament\Resources\Candidates\Schemas\CandidateForm;
use App\Filament\Resources\Candidates\Schemas\CandidateInfolist;
use App\Filament\Resources\Candidates\Tables\CandidatesTable;
use App\Models\Candidate;
use App\Models\ParentAccount;
use App\Models\Team;
use App\Services\CandidateDataNormalizer;
use App\Services\PointsEngine;
use App\Services\RecruitmentStageGuard;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

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
            PointTransactionsRelationManager::class,
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
        return app(CandidateDataNormalizer::class)->normalize($data);
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

    public static function makeAdjustPointsAction(): Action
    {
        return Action::make('adjustPoints')
            ->label('Adjust points')
            ->icon(Heroicon::OutlinedCurrencyDollar)
            ->color('info')
            ->visible(fn (): bool => auth()->user()?->hasRole(['Admin', 'Management']) ?? false)
            ->schema([
                TextInput::make('points')
                    ->label('Points (signed)')
                    ->numeric()
                    ->required()
                    ->helperText('Positive to credit, negative to debit.'),
                Textarea::make('reason')
                    ->required()
                    ->maxLength(1000),
            ])
            ->action(function (Candidate $record, array $data): void {
                app(PointsEngine::class)->adjust(
                    $record,
                    (int) $data['points'],
                    $data['reason'],
                    auth()->user(),
                );
            });
    }

    public static function makeInviteParentAction(): Action
    {
        return Action::make('inviteParent')
            ->label('Invite parent')
            ->icon(Heroicon::OutlinedEnvelope)
            ->color('info')
            ->disabled(fn (Candidate $record): bool => ! $record->is_player)
            ->action(function (Candidate $record): void {
                if (! $record->is_player) {
                    Notification::make()
                        ->danger()
                        ->title('Only players can be linked to parent accounts')
                        ->send();

                    throw (new Halt)->rollBackDatabaseTransaction();
                }

                if (blank($record->email)) {
                    Notification::make()
                        ->danger()
                        ->title('Parent email is required before sending an invitation')
                        ->send();

                    throw (new Halt)->rollBackDatabaseTransaction();
                }

                $parent = ParentAccount::query()->firstOrNew([
                    'email' => $record->email,
                ]);

                $parent->fill([
                    'name' => $record->parent_name,
                    'phone' => $record->parent_phone,
                    'whatsapp' => $record->parent_whatsapp,
                ]);

                if (! $parent->exists || ! $parent->accepted_at) {
                    $parent->invitation_token = Str::random(64);
                    $parent->invited_at = now();
                    $parent->accepted_at = null;
                    $parent->password = null;
                }

                $parent->save();
                $parent->players()->syncWithoutDetaching([$record->id]);

                Notification::make()
                    ->success()
                    ->title('Parent account linked')
                    ->body('The parent account is ready for the mobile invite flow.')
                    ->send();
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
