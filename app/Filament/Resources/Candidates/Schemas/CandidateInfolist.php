<?php

namespace App\Filament\Resources\Candidates\Schemas;

use App\Enums\DocumentStatus;
use App\Enums\FederationStatus;
use App\Enums\JoiningStatus;
use App\Enums\PlayingPosition;
use App\Enums\RecruitmentStage;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CandidateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.candidates.sections.candidate'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('full_name')
                            ->label(__('admin.resources.candidates.fields.full_name')),
                        TextEntry::make('playing_position')
                            ->label(__('admin.resources.candidates.fields.playing_position'))
                            ->formatStateUsing(fn ($state) => $state instanceof PlayingPosition ? $state->getLabel() : PlayingPosition::tryFrom((string) $state)?->getLabel()),
                        TextEntry::make('date_of_birth')
                            ->label(__('admin.resources.candidates.fields.date_of_birth'))
                            ->date(),
                        TextEntry::make('year_of_birth')
                            ->label(__('admin.resources.candidates.fields.year_of_birth')),
                        TextEntry::make('country_of_birth')
                            ->label(__('admin.resources.candidates.fields.country_of_birth')),
                        TextEntry::make('citizenship')
                            ->label(__('admin.resources.candidates.fields.citizenship')),
                        TextEntry::make('year_arrived_qatar')
                            ->label(__('admin.resources.candidates.fields.year_arrived_qatar')),
                        TextEntry::make('school')
                            ->label(__('admin.resources.candidates.fields.school')),
                        TextEntry::make('previous_club')
                            ->label(__('admin.resources.candidates.fields.previous_club')),
                        TextEntry::make('season.name')
                            ->label(__('admin.common.season')),
                        TextEntry::make('team.name')
                            ->label(__('admin.common.team')),
                        IconEntry::make('is_player')
                            ->label(__('admin.resources.candidates.fields.is_player'))
                            ->boolean(),
                    ]),
                Section::make(__('admin.resources.candidates.sections.parent'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('parent_name')
                            ->label(__('admin.resources.candidates.fields.parent_name')),
                        TextEntry::make('parent_phone')
                            ->label(__('admin.resources.candidates.fields.parent_phone')),
                        TextEntry::make('parent_whatsapp')
                            ->label(__('admin.resources.candidates.fields.parent_whatsapp')),
                        TextEntry::make('email')
                            ->label(__('admin.resources.candidates.fields.email')),
                    ]),
                Section::make(__('admin.resources.candidates.sections.statuses'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('recruitment_stage')
                            ->label(__('admin.resources.candidates.fields.recruitment_stage'))
                            ->formatStateUsing(fn ($state) => $state instanceof RecruitmentStage ? $state->getLabel() : RecruitmentStage::tryFrom((string) $state)?->getLabel())
                            ->badge(),
                        TextEntry::make('document_status')
                            ->label(__('admin.resources.candidates.fields.document_status'))
                            ->formatStateUsing(fn ($state) => $state instanceof DocumentStatus ? $state->getLabel() : DocumentStatus::tryFrom((string) $state)?->getLabel())
                            ->badge(),
                        TextEntry::make('qfa_status')
                            ->label(__('admin.resources.candidates.fields.qfa_status'))
                            ->formatStateUsing(fn ($state) => $state instanceof FederationStatus ? $state->getLabel() : FederationStatus::tryFrom((string) $state)?->getLabel())
                            ->badge(),
                        TextEntry::make('fifa_status')
                            ->label(__('admin.resources.candidates.fields.fifa_status'))
                            ->formatStateUsing(fn ($state) => $state instanceof FederationStatus ? $state->getLabel() : FederationStatus::tryFrom((string) $state)?->getLabel())
                            ->badge(),
                        TextEntry::make('joining_status')
                            ->label(__('admin.resources.candidates.fields.joining_status'))
                            ->formatStateUsing(fn ($state) => $state instanceof JoiningStatus ? $state->getLabel() : JoiningStatus::tryFrom((string) $state)?->getLabel())
                            ->badge(),
                        TextEntry::make('statusUpdatedBy.name')
                            ->label(__('admin.resources.candidates.fields.status_updated_by')),
                        TextEntry::make('status_updated_at')
                            ->label(__('admin.resources.candidates.fields.status_updated_at'))
                            ->dateTime(),
                    ]),
                Section::make(__('admin.resources.candidates.sections.points'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('points_balance')
                            ->label(__('admin.resources.candidates.fields.points_balance'))
                            ->state(fn ($record) => $record->pointsBalance()),
                    ]),
                Section::make(__('admin.resources.candidates.sections.consent'))
                    ->columns(2)
                    ->schema([
                        IconEntry::make('consent_given')
                            ->label(__('admin.resources.candidates.fields.consent_given'))
                            ->boolean(),
                        TextEntry::make('consent_at')
                            ->label(__('admin.resources.candidates.fields.consent_at'))
                            ->dateTime(),
                        TextEntry::make('notes')
                            ->label(__('admin.resources.candidates.fields.notes'))
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
