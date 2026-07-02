<?php

namespace App\Filament\Resources\Candidates\Schemas;

use App\Enums\DocumentStatus;
use App\Enums\FederationStatus;
use App\Enums\JoiningStatus;
use App\Enums\PlayingPosition;
use App\Enums\RecruitmentStage;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class CandidateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        // Two independently-stacked columns so a short card is followed by the
        // next card in the same column instead of leaving a vertical gap.
        return $schema
            ->components([
                Group::make([
                    self::candidateSection(),
                    self::consentSection(),
                ])->columns(1),
                Group::make([
                    self::parentSection(),
                    self::statusesSection(),
                    self::pointsSection(),
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
                TextEntry::make('full_name')
                    ->label(__('admin.resources.candidates.fields.full_name'))
                    ->weight(FontWeight::Bold)
                    ->icon(Heroicon::OutlinedUser)
                    ->columnSpanFull(),
                TextEntry::make('playing_position')
                    ->label(__('admin.resources.candidates.fields.playing_position'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof PlayingPosition ? $state->getLabel() : PlayingPosition::tryFrom((string) $state)?->getLabel())
                    ->color(fn ($state) => ($state instanceof PlayingPosition ? $state : PlayingPosition::tryFrom((string) $state))?->getColor()),
                TextEntry::make('date_of_birth')
                    ->label(__('admin.resources.candidates.fields.date_of_birth'))
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->date(),
                TextEntry::make('year_of_birth')
                    ->label(__('admin.resources.candidates.fields.year_of_birth')),
                TextEntry::make('year_arrived_qatar')
                    ->label(__('admin.resources.candidates.fields.year_arrived_qatar')),
                TextEntry::make('country_of_birth')
                    ->label(__('admin.resources.candidates.fields.country_of_birth'))
                    ->icon(Heroicon::OutlinedGlobeAlt),
                TextEntry::make('citizenship')
                    ->label(__('admin.resources.candidates.fields.citizenship'))
                    ->icon(Heroicon::OutlinedFlag),
                TextEntry::make('school')
                    ->label(__('admin.resources.candidates.fields.school'))
                    ->icon(Heroicon::OutlinedAcademicCap),
                TextEntry::make('previous_club')
                    ->label(__('admin.resources.candidates.fields.previous_club')),
                TextEntry::make('season.name')
                    ->label(__('admin.common.season'))
                    ->icon(Heroicon::OutlinedCalendar),
                TextEntry::make('team.name')
                    ->label(__('admin.common.team'))
                    ->icon(Heroicon::OutlinedUserGroup)
                    ->placeholder(__('admin.common.not_available')),
                IconEntry::make('is_player')
                    ->label(__('admin.resources.candidates.fields.is_player'))
                    ->boolean(),
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
                TextEntry::make('parent_name')
                    ->label(__('admin.resources.candidates.fields.parent_name'))
                    ->icon(Heroicon::OutlinedUser),
                TextEntry::make('email')
                    ->label(__('admin.resources.candidates.fields.email'))
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->copyable()
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('parent_phone')
                    ->label(__('admin.resources.candidates.fields.parent_phone'))
                    ->icon(Heroicon::OutlinedPhone)
                    ->copyable(),
                TextEntry::make('parent_whatsapp')
                    ->label(__('admin.resources.candidates.fields.parent_whatsapp'))
                    ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                    ->copyable(),
            ]);
    }

    protected static function statusesSection(): Section
    {
        return Section::make(__('admin.resources.candidates.sections.statuses'))
            ->description(__('admin.resources.candidates.descriptions.statuses'))
            ->icon(Heroicon::OutlinedClipboardDocumentCheck)
            ->iconColor('primary')
            ->columns(2)
            ->schema([
                TextEntry::make('recruitment_stage')
                    ->label(__('admin.resources.candidates.fields.recruitment_stage'))
                    ->formatStateUsing(fn ($state) => $state instanceof RecruitmentStage ? $state->getLabel() : RecruitmentStage::tryFrom((string) $state)?->getLabel())
                    ->color(fn ($state) => ($state instanceof RecruitmentStage ? $state : RecruitmentStage::tryFrom((string) $state))?->getColor())
                    ->badge(),
                TextEntry::make('document_status')
                    ->label(__('admin.resources.candidates.fields.document_status'))
                    ->formatStateUsing(fn ($state) => $state instanceof DocumentStatus ? $state->getLabel() : DocumentStatus::tryFrom((string) $state)?->getLabel())
                    ->color(fn ($state) => ($state instanceof DocumentStatus ? $state : DocumentStatus::tryFrom((string) $state))?->getColor())
                    ->badge(),
                TextEntry::make('qfa_status')
                    ->label(__('admin.resources.candidates.fields.qfa_status'))
                    ->formatStateUsing(fn ($state) => $state instanceof FederationStatus ? $state->getLabel() : FederationStatus::tryFrom((string) $state)?->getLabel())
                    ->color(fn ($state) => ($state instanceof FederationStatus ? $state : FederationStatus::tryFrom((string) $state))?->getColor())
                    ->badge(),
                TextEntry::make('fifa_status')
                    ->label(__('admin.resources.candidates.fields.fifa_status'))
                    ->formatStateUsing(fn ($state) => $state instanceof FederationStatus ? $state->getLabel() : FederationStatus::tryFrom((string) $state)?->getLabel())
                    ->color(fn ($state) => ($state instanceof FederationStatus ? $state : FederationStatus::tryFrom((string) $state))?->getColor())
                    ->badge(),
                TextEntry::make('joining_status')
                    ->label(__('admin.resources.candidates.fields.joining_status'))
                    ->formatStateUsing(fn ($state) => $state instanceof JoiningStatus ? $state->getLabel() : JoiningStatus::tryFrom((string) $state)?->getLabel())
                    ->color(fn ($state) => ($state instanceof JoiningStatus ? $state : JoiningStatus::tryFrom((string) $state))?->getColor())
                    ->badge(),
                TextEntry::make('statusUpdatedBy.name')
                    ->label(__('admin.resources.candidates.fields.status_updated_by'))
                    ->icon(Heroicon::OutlinedUser)
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('status_updated_at')
                    ->label(__('admin.resources.candidates.fields.status_updated_at'))
                    ->icon(Heroicon::OutlinedClock)
                    ->dateTime()
                    ->placeholder(__('admin.common.not_available')),
            ]);
    }

    protected static function pointsSection(): Section
    {
        return Section::make(__('admin.resources.candidates.sections.points'))
            ->description(__('admin.resources.candidates.descriptions.points'))
            ->icon(Heroicon::OutlinedStar)
            ->iconColor('primary')
            ->columns(2)
            ->schema([
                TextEntry::make('points_balance')
                    ->label(__('admin.resources.candidates.fields.points_balance'))
                    ->state(fn ($record) => $record->pointsBalance())
                    ->badge()
                    ->color('warning')
                    ->icon(Heroicon::OutlinedStar),
            ]);
    }

    protected static function consentSection(): Section
    {
        return Section::make(__('admin.resources.candidates.sections.consent'))
            ->description(__('admin.resources.candidates.descriptions.consent'))
            ->icon(Heroicon::OutlinedShieldCheck)
            ->iconColor('primary')
            ->columns(2)
            ->schema([
                IconEntry::make('consent_given')
                    ->label(__('admin.resources.candidates.fields.consent_given'))
                    ->boolean(),
                TextEntry::make('consent_at')
                    ->label(__('admin.resources.candidates.fields.consent_at'))
                    ->icon(Heroicon::OutlinedClock)
                    ->dateTime()
                    ->placeholder(__('admin.common.not_available')),
                TextEntry::make('notes')
                    ->label(__('admin.resources.candidates.fields.notes'))
                    ->placeholder(__('admin.common.not_available'))
                    ->columnSpanFull(),
            ]);
    }
}
