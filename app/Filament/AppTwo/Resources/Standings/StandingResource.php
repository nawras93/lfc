<?php

namespace App\Filament\AppTwo\Resources\Standings;

use App\Filament\AppTwo\Resources\Standings\Pages\CreateStanding;
use App\Filament\AppTwo\Resources\Standings\Pages\EditStanding;
use App\Filament\AppTwo\Resources\Standings\Pages\ListStandings;
use App\Models\Standing;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StandingResource extends Resource
{
    protected static ?string $model = Standing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.standings.sections.club'))
                    ->description(__('admin.resources.standings.descriptions.club'))
                    ->icon(Heroicon::OutlinedTableCells)
                    ->iconColor('primary')
                    ->columns(2)
                    ->schema([
                        TextInput::make('club_name')
                            ->label(__('admin.resources.standings.fields.club_name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('club_name_ar')
                            ->label(__('admin.resources.standings.fields.club_name_ar'))
                            ->maxLength(255)
                            ->hint(__('admin.common.arabic'))
                            ->extraInputAttributes(['dir' => 'rtl']),
                        TextInput::make('played')
                            ->label(__('admin.resources.standings.fields.played'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('won')
                            ->label(__('admin.resources.standings.fields.won'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('drawn')
                            ->label(__('admin.resources.standings.fields.drawn'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('lost')
                            ->label(__('admin.resources.standings.fields.lost'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('goals_for')
                            ->label(__('admin.resources.standings.fields.goals_for'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('goals_against')
                            ->label(__('admin.resources.standings.fields.goals_against'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        TextInput::make('points')
                            ->label(__('admin.resources.standings.fields.points'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0),
                        Toggle::make('is_own_club')
                            ->label(__('admin.resources.standings.fields.is_own_club')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('club_name')
                    ->label(__('admin.resources.standings.fields.club_name'))
                    ->state(fn (Standing $record): ?string => $record->localized('club_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('points')
                    ->label(__('admin.resources.standings.fields.points'))
                    ->sortable(),
                TextColumn::make('goal_difference')
                    ->label(__('admin.resources.standings.fields.goal_difference'))
                    ->state(fn (Standing $record): int => $record->goal_difference)
                    ->sortable(query: fn ($query, string $direction) => $query->orderByRaw('(goals_for - goals_against) '.$direction)),
                IconColumn::make('is_own_club')
                    ->label(__('admin.resources.standings.fields.is_own_club'))
                    ->boolean(),
            ])
            ->defaultSort('points', 'desc')
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStandings::route('/'),
            'create' => CreateStanding::route('/create'),
            'edit' => EditStanding::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.standings.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.standings.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.standings.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.groups.content');
    }
}
