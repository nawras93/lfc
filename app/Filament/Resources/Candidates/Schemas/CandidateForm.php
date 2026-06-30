<?php

namespace App\Filament\Resources\Candidates\Schemas;

use App\Enums\DocumentStatus;
use App\Enums\FederationStatus;
use App\Enums\JoiningStatus;
use App\Enums\PlayingPosition;
use App\Enums\RecruitmentStage;
use App\Models\Season;
use App\Models\Team;
use App\Support\EnumOptions;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CandidateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Candidate')
                    ->columns(2)
                    ->schema([
                        TextInput::make('full_name')
                            ->required()
                            ->maxLength(255),
                        Select::make('playing_position')
                            ->options(EnumOptions::for(PlayingPosition::class))
                            ->required()
                            ->native(false),
                        TextInput::make('year_of_birth')
                            ->numeric()
                            ->required()
                            ->minValue(1990)
                            ->maxValue((int) now()->format('Y')),
                        DatePicker::make('date_of_birth')
                            ->required(),
                        TextInput::make('country_of_birth')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('citizenship')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('year_arrived_qatar')
                            ->numeric()
                            ->required()
                            ->minValue(1990)
                            ->maxValue((int) now()->format('Y')),
                        TextInput::make('school')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('previous_club')
                            ->required()
                            ->maxLength(255),
                        Select::make('season_id')
                            ->label('Season')
                            ->options(fn (): array => Season::query()->orderByDesc('is_active')->orderBy('name')->pluck('name', 'id')->all())
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('team_id')
                            ->label('Team')
                            ->options(fn (): array => Team::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload(),
                    ]),
                Section::make('Parent')
                    ->columns(2)
                    ->schema([
                        TextInput::make('parent_name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('parent_phone')
                            ->required()
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('parent_whatsapp')
                            ->required()
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                    ]),
                Section::make('Workflow')
                    ->columns(2)
                    ->schema([
                        Select::make('recruitment_stage')
                            ->options(EnumOptions::for(RecruitmentStage::class))
                            ->required(fn (string $operation): bool => $operation !== 'create')
                            ->default(RecruitmentStage::NewApplication->value)
                            ->native(false)
                            ->visible(fn (string $operation): bool => $operation !== 'create'),
                        Select::make('document_status')
                            ->options(EnumOptions::for(DocumentStatus::class))
                            ->required()
                            ->default(DocumentStatus::Pending->value)
                            ->native(false),
                        Select::make('qfa_status')
                            ->options(EnumOptions::for(FederationStatus::class))
                            ->required()
                            ->default(FederationStatus::NotStarted->value)
                            ->native(false),
                        Select::make('fifa_status')
                            ->options(EnumOptions::for(FederationStatus::class))
                            ->required()
                            ->default(FederationStatus::NotStarted->value)
                            ->native(false),
                        Select::make('joining_status')
                            ->options(EnumOptions::for(JoiningStatus::class))
                            ->required()
                            ->default(JoiningStatus::NotStarted->value)
                            ->native(false),
                        Toggle::make('is_player')
                            ->disabled(),
                    ]),
                Section::make('Consent & notes')
                    ->columns(2)
                    ->schema([
                        Toggle::make('consent_given')
                            ->label('Consent captured'),
                        TextInput::make('consent_at')
                            ->disabled()
                            ->dehydrated(false),
                        Textarea::make('notes')
                            ->rows(5)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function recruitmentStageActionSchema(): array
    {
        return [
            Select::make('recruitment_stage')
                ->label('Recruitment stage')
                ->options(EnumOptions::for(RecruitmentStage::class))
                ->required()
                ->native(false),
            Textarea::make('note')
                ->rows(3)
                ->maxLength(1000),
        ];
    }
}
