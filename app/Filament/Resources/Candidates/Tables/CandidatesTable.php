<?php

namespace App\Filament\Resources\Candidates\Tables;

use App\Enums\PlayingPosition;
use App\Enums\RecruitmentStage;
use App\Models\Season;
use App\Models\Team;
use App\Support\EnumOptions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CandidatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label(__('admin.resources.candidates.fields.full_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('parent_phone')
                    ->label(__('admin.resources.candidates.fields.parent_phone'))
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('recruitment_stage')
                    ->label(__('admin.resources.candidates.fields.recruitment_stage'))
                    ->badge()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('playing_position')
                    ->label(__('admin.resources.candidates.fields.playing_position'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('season.name')
                    ->label(__('admin.common.season'))
                    ->sortable(),
                TextColumn::make('team.name')
                    ->label(__('admin.common.team'))
                    ->sortable(),
                IconColumn::make('is_player')
                    ->boolean()
                    ->label(__('admin.resources.candidates.fields.is_player')),
            ])
            ->filters([
                SelectFilter::make('recruitment_stage')
                    ->label(__('admin.resources.candidates.fields.recruitment_stage'))
                    ->options(EnumOptions::for(RecruitmentStage::class)),
                SelectFilter::make('playing_position')
                    ->label(__('admin.resources.candidates.fields.playing_position'))
                    ->options(EnumOptions::for(PlayingPosition::class)),
                SelectFilter::make('season_id')
                    ->label(__('admin.common.season'))
                    ->options(fn (): array => Season::query()->orderByDesc('is_active')->orderBy('name')->pluck('name', 'id')->all()),
                SelectFilter::make('team_id')
                    ->label(__('admin.common.team'))
                    ->options(fn (): array => Team::query()->orderBy('name')->pluck('name', 'id')->all()),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
