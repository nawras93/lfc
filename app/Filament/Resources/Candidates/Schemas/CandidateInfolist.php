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
                Section::make('Candidate')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('full_name'),
                        TextEntry::make('playing_position')
                            ->formatStateUsing(fn ($state) => $state instanceof PlayingPosition ? $state->getLabel() : PlayingPosition::tryFrom((string) $state)?->getLabel()),
                        TextEntry::make('date_of_birth')
                            ->date(),
                        TextEntry::make('year_of_birth'),
                        TextEntry::make('country_of_birth'),
                        TextEntry::make('citizenship'),
                        TextEntry::make('year_arrived_qatar'),
                        TextEntry::make('school'),
                        TextEntry::make('previous_club'),
                        TextEntry::make('season.name')
                            ->label('Season'),
                        TextEntry::make('team.name')
                            ->label('Team'),
                        IconEntry::make('is_player')
                            ->boolean(),
                    ]),
                Section::make('Parent')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('parent_name'),
                        TextEntry::make('parent_phone'),
                        TextEntry::make('parent_whatsapp'),
                        TextEntry::make('email'),
                    ]),
                Section::make('Statuses')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('recruitment_stage')
                            ->formatStateUsing(fn ($state) => $state instanceof RecruitmentStage ? $state->getLabel() : RecruitmentStage::tryFrom((string) $state)?->getLabel())
                            ->badge(),
                        TextEntry::make('document_status')
                            ->formatStateUsing(fn ($state) => $state instanceof DocumentStatus ? $state->getLabel() : DocumentStatus::tryFrom((string) $state)?->getLabel())
                            ->badge(),
                        TextEntry::make('qfa_status')
                            ->formatStateUsing(fn ($state) => $state instanceof FederationStatus ? $state->getLabel() : FederationStatus::tryFrom((string) $state)?->getLabel())
                            ->badge(),
                        TextEntry::make('fifa_status')
                            ->formatStateUsing(fn ($state) => $state instanceof FederationStatus ? $state->getLabel() : FederationStatus::tryFrom((string) $state)?->getLabel())
                            ->badge(),
                        TextEntry::make('joining_status')
                            ->formatStateUsing(fn ($state) => $state instanceof JoiningStatus ? $state->getLabel() : JoiningStatus::tryFrom((string) $state)?->getLabel())
                            ->badge(),
                        TextEntry::make('statusUpdatedBy.name')
                            ->label('Status updated by'),
                        TextEntry::make('status_updated_at')
                            ->dateTime(),
                    ]),
                Section::make('Consent')
                    ->columns(2)
                    ->schema([
                        IconEntry::make('consent_given')
                            ->boolean(),
                        TextEntry::make('consent_at')
                            ->dateTime(),
                        TextEntry::make('notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
