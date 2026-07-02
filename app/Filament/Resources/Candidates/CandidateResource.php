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

    public static function getModelLabel(): string
    {
        return __('admin.resources.candidates.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.candidates.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.candidates.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.groups.recruitment');
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
            ->label(__('admin.resources.candidates.actions.change_recruitment_stage'))
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
                        ->title(__('admin.resources.candidates.messages.invalid_transition_title'))
                        ->body($exception->getMessage())
                        ->send();

                    throw (new Halt)->rollBackDatabaseTransaction();
                }
            });
    }

    public static function makeMarkAsPlayerAction(): Action
    {
        return Action::make('markAsPlayer')
            ->label(__('admin.resources.candidates.actions.mark_as_player'))
            ->icon(Heroicon::OutlinedUserPlus)
            ->color('success')
            ->disabled(fn (Candidate $record): bool => ! $record->canBeMarkedAsPlayer())
            ->schema([
                Select::make('team_id')
                    ->label(__('admin.common.team'))
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
                        ->title(__('admin.resources.candidates.messages.not_eligible_player'))
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
            ->label(__('admin.resources.candidates.actions.adjust_points'))
            ->icon(Heroicon::OutlinedCurrencyDollar)
            ->color('info')
            ->visible(fn (): bool => auth()->user()?->hasRole(['Admin', 'Management']) ?? false)
            ->schema([
                TextInput::make('points')
                    ->label(__('admin.resources.candidates.fields.points_signed'))
                    ->numeric()
                    ->required()
                    ->helperText(__('admin.resources.candidates.helper.adjust_points')),
                Textarea::make('reason')
                    ->label(__('admin.resources.candidates.fields.reason'))
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
            ->label(__('admin.resources.candidates.actions.invite_parent'))
            ->icon(Heroicon::OutlinedEnvelope)
            ->color('info')
            ->disabled(fn (Candidate $record): bool => ! $record->is_player)
            ->action(function (Candidate $record): void {
                if (! $record->is_player) {
                    Notification::make()
                        ->danger()
                        ->title(__('admin.resources.candidates.messages.only_players_invite'))
                        ->send();

                    throw (new Halt)->rollBackDatabaseTransaction();
                }

                if (blank($record->email)) {
                    Notification::make()
                        ->danger()
                        ->title(__('admin.resources.candidates.messages.parent_email_required'))
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
                    ->title(__('admin.resources.candidates.messages.parent_linked_title'))
                    ->body(__('admin.resources.candidates.messages.parent_linked_body'))
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
            __('admin.resources.candidates.search.parent_phone') => $record->parent_phone,
            __('admin.resources.candidates.search.recruitment_stage') => $record->recruitment_stage->getLabel(),
        ];
    }
}
