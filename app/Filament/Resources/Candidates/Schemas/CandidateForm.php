<?php

namespace App\Filament\Resources\Candidates\Schemas;

use App\Enums\DocumentStatus;
use App\Enums\FederationStatus;
use App\Enums\JoiningStatus;
use App\Enums\PlayingPosition;
use App\Enums\RecruitmentStage;
use App\Models\Season;
use App\Models\Team;
use App\Rules\LatinText;
use App\Support\EnumOptions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class CandidateForm
{
    public static function configure(Schema $schema): Schema
    {
        // Two independently-stacked columns so the shorter cards pack under one
        // another beside the tall candidate card instead of leaving a gap.
        return $schema
            ->components([
                Group::make([
                    self::candidateSection(),
                ])->columns(1),
                Group::make([
                    self::parentSection(),
                    self::workflowSection(),
                    self::consentSection(),
                ])->columns(1),
            ]);
    }

    protected static function candidateSection(): Section
    {
        return Section::make(__('admin.resources.candidates.sections.candidate'))
            ->description(__('admin.resources.candidates.descriptions.candidate'))
            ->icon(Heroicon::OutlinedIdentification)
            ->iconColor('primary')
            ->columns(2)
            ->schema([
                TextInput::make('full_name')
                    ->label(__('admin.resources.candidates.fields.full_name'))
                    ->required()
                    ->maxLength(255)
                    ->rule(new LatinText)
                    ->prefixIcon(Heroicon::OutlinedUser)
                    ->columnSpanFull(),
                Select::make('playing_position')
                    ->label(__('admin.resources.candidates.fields.playing_position'))
                    ->options(EnumOptions::for(PlayingPosition::class))
                    ->required()
                    ->native(false),
                DatePicker::make('date_of_birth')
                    ->label(__('admin.resources.candidates.fields.date_of_birth'))
                    ->required(),
                TextInput::make('year_of_birth')
                    ->label(__('admin.resources.candidates.fields.year_of_birth'))
                    ->numeric()
                    ->required()
                    ->minValue(1990)
                    ->maxValue((int) now()->format('Y'))
                    ->placeholder('2015'),
                TextInput::make('year_arrived_qatar')
                    ->label(__('admin.resources.candidates.fields.year_arrived_qatar'))
                    ->numeric()
                    ->required()
                    ->minValue(1990)
                    ->maxValue((int) now()->format('Y'))
                    ->placeholder(now()->format('Y')),
                TextInput::make('country_of_birth')
                    ->label(__('admin.resources.candidates.fields.country_of_birth'))
                    ->required()
                    ->maxLength(255)
                    ->rule(new LatinText)
                    ->prefixIcon(Heroicon::OutlinedGlobeAlt),
                TextInput::make('citizenship')
                    ->label(__('admin.resources.candidates.fields.citizenship'))
                    ->required()
                    ->maxLength(255)
                    ->rule(new LatinText)
                    ->prefixIcon(Heroicon::OutlinedFlag),
                TextInput::make('school')
                    ->label(__('admin.resources.candidates.fields.school'))
                    ->required()
                    ->maxLength(255)
                    ->rule(new LatinText)
                    ->prefixIcon(Heroicon::OutlinedAcademicCap),
                TextInput::make('previous_club')
                    ->label(__('admin.resources.candidates.fields.previous_club'))
                    ->required()
                    ->maxLength(255)
                    ->rule(new LatinText),
                Select::make('season_id')
                    ->label(__('admin.common.season'))
                    ->options(fn (): array => Season::query()->orderByDesc('is_active')->orderBy('name')->pluck('name', 'id')->all())
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('team_id')
                    ->label(__('admin.common.team'))
                    ->options(fn (): array => Team::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload(),
            ]);
    }

    protected static function parentSection(): Section
    {
        return Section::make(__('admin.resources.candidates.sections.parent'))
            ->description(__('admin.resources.candidates.descriptions.parent'))
            ->icon(Heroicon::OutlinedUserGroup)
            ->iconColor('primary')
            ->columns(2)
            ->schema([
                TextInput::make('parent_name')
                    ->label(__('admin.resources.candidates.fields.parent_name'))
                    ->required()
                    ->maxLength(255)
                    ->rule(new LatinText)
                    ->prefixIcon(Heroicon::OutlinedUser),
                TextInput::make('email')
                    ->label(__('admin.resources.candidates.fields.email'))
                    ->email()
                    ->maxLength(255)
                    ->prefixIcon(Heroicon::OutlinedEnvelope),
                TextInput::make('parent_phone')
                    ->label(__('admin.resources.candidates.fields.parent_phone'))
                    ->required()
                    ->tel()
                    ->maxLength(255)
                    ->prefixIcon(Heroicon::OutlinedPhone),
                TextInput::make('parent_whatsapp')
                    ->label(__('admin.resources.candidates.fields.parent_whatsapp'))
                    ->required()
                    ->tel()
                    ->maxLength(255)
                    ->prefixIcon(Heroicon::OutlinedChatBubbleLeftRight),
            ]);
    }

    protected static function workflowSection(): Section
    {
        return Section::make(__('admin.resources.candidates.sections.workflow'))
            ->description(__('admin.resources.candidates.descriptions.workflow'))
            ->icon(Heroicon::OutlinedClipboardDocumentCheck)
            ->iconColor('primary')
            ->columns(2)
            ->schema([
                Select::make('recruitment_stage')
                    ->label(__('admin.resources.candidates.fields.recruitment_stage'))
                    ->options(EnumOptions::for(RecruitmentStage::class))
                    ->required(fn (string $operation): bool => $operation !== 'create')
                    ->default(RecruitmentStage::NewApplication->value)
                    ->native(false)
                    ->visible(fn (string $operation): bool => $operation !== 'create'),
                Select::make('document_status')
                    ->label(__('admin.resources.candidates.fields.document_status'))
                    ->options(EnumOptions::for(DocumentStatus::class))
                    ->required()
                    ->default(DocumentStatus::Pending->value)
                    ->native(false),
                Select::make('qfa_status')
                    ->label(__('admin.resources.candidates.fields.qfa_status'))
                    ->options(EnumOptions::for(FederationStatus::class))
                    ->required()
                    ->default(FederationStatus::NotStarted->value)
                    ->native(false),
                Select::make('fifa_status')
                    ->label(__('admin.resources.candidates.fields.fifa_status'))
                    ->options(EnumOptions::for(FederationStatus::class))
                    ->required()
                    ->default(FederationStatus::NotStarted->value)
                    ->native(false),
                Select::make('joining_status')
                    ->label(__('admin.resources.candidates.fields.joining_status'))
                    ->options(EnumOptions::for(JoiningStatus::class))
                    ->required()
                    ->default(JoiningStatus::NotStarted->value)
                    ->native(false),
                Toggle::make('is_player')
                    ->label(__('admin.resources.candidates.fields.is_player'))
                    ->disabled(),
            ]);
    }

    protected static function consentSection(): Section
    {
        return Section::make(__('admin.resources.candidates.sections.consent_notes'))
            ->description(__('admin.resources.candidates.descriptions.consent_notes'))
            ->icon(Heroicon::OutlinedShieldCheck)
            ->iconColor('primary')
            ->columns(2)
            ->schema([
                Toggle::make('consent_given')
                    ->label(__('admin.resources.candidates.fields.consent_given')),
                TextInput::make('consent_at')
                    ->label(__('admin.resources.candidates.fields.consent_at'))
                    ->disabled()
                    ->dehydrated(false)
                    ->prefixIcon(Heroicon::OutlinedClock),
                Textarea::make('notes')
                    ->label(__('admin.resources.candidates.fields.notes'))
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }

    public static function recruitmentStageActionSchema(): array
    {
        return [
            Select::make('recruitment_stage')
                ->label(__('admin.resources.candidates.fields.recruitment_stage'))
                ->options(EnumOptions::for(RecruitmentStage::class))
                ->required()
                ->native(false),
            Textarea::make('note')
                ->label(__('admin.resources.candidates.fields.note'))
                ->rows(3)
                ->maxLength(1000),
        ];
    }
}
