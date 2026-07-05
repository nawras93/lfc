<?php

namespace App\Filament\AppTwo\Resources\Matches;

use App\Enums\FixtureStatus;
use App\Filament\AppTwo\Resources\Matches\Pages\CreateMatch;
use App\Filament\AppTwo\Resources\Matches\Pages\EditMatch;
use App\Filament\AppTwo\Resources\Matches\Pages\ListMatches;
use App\Models\Fixture;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MatchResource extends Resource
{
    protected static ?string $model = Fixture::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.matches.sections.match'))
                    ->description(__('admin.resources.matches.descriptions.match'))
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->iconColor('primary')
                    ->columns(2)
                    ->schema([
                        TextInput::make('opponent')
                            ->label(__('admin.resources.matches.fields.opponent'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('competition')
                            ->label(__('admin.resources.matches.fields.competition'))
                            ->maxLength(255),
                        Toggle::make('is_home')
                            ->label(__('admin.resources.matches.fields.is_home'))
                            ->default(true),
                        TextInput::make('venue')
                            ->label(__('admin.resources.matches.fields.venue'))
                            ->required()
                            ->maxLength(255),
                        DateTimePicker::make('kickoff_at')
                            ->label(__('admin.resources.matches.fields.kickoff_at'))
                            ->required(),
                        Select::make('status')
                            ->label(__('admin.common.status'))
                            ->options(FixtureStatus::class)
                            ->required()
                            ->native(false),
                        TextInput::make('our_score')
                            ->label(__('admin.resources.matches.fields.our_score'))
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('opponent_score')
                            ->label(__('admin.resources.matches.fields.opponent_score'))
                            ->numeric()
                            ->minValue(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kickoff_at')
                    ->label(__('admin.resources.matches.fields.kickoff_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('opponent')
                    ->label(__('admin.resources.matches.fields.opponent'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('competition')
                    ->label(__('admin.resources.matches.fields.competition'))
                    ->toggleable(),
                IconColumn::make('is_home')
                    ->label(__('admin.resources.matches.fields.is_home'))
                    ->boolean(),
                TextColumn::make('score')
                    ->label(__('admin.resources.matches.fields.score'))
                    ->state(fn (Fixture $record): string => $record->isPlayed()
                        ? $record->our_score.'-'.$record->opponent_score
                        : __('admin.common.not_available')),
                TextColumn::make('status')
                    ->label(__('admin.common.status'))
                    ->badge()
                    ->sortable(),
            ])
            ->defaultSort('kickoff_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMatches::route('/'),
            'create' => CreateMatch::route('/create'),
            'edit' => EditMatch::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNull('team_id');
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.matches.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.matches.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.matches.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.groups.content');
    }
}
