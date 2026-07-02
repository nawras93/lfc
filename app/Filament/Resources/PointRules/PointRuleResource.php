<?php

namespace App\Filament\Resources\PointRules;

use App\Enums\PointRuleType;
use App\Models\PointRule;
use App\Models\Season;
use App\Models\Team;
use App\Support\EnumOptions;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PointRuleResource extends Resource
{
    protected static ?string $model = PointRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.point_rules.sections.rule'))
                    ->description(__('admin.resources.point_rules.descriptions.rule'))
                    ->icon(Heroicon::OutlinedStar)
                    ->iconColor('primary')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('admin.resources.point_rules.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Radio::make('type')
                            ->label(__('admin.resources.point_rules.fields.type'))
                            ->options(EnumOptions::for(PointRuleType::class))
                            ->required()
                            ->live()
                            ->columnSpanFull(),
                        TextInput::make('points')
                            ->label(__('admin.resources.point_rules.fields.points'))
                            ->numeric()
                            ->minValue(1)
                            ->prefixIcon(Heroicon::OutlinedStar)
                            ->suffix(__('admin.common.points'))
                            ->required(fn ($get): bool => $get('type') === PointRuleType::Fixed->value)
                            ->visible(fn ($get): bool => $get('type') === PointRuleType::Fixed->value),
                        TextInput::make('percentage')
                            ->label(__('admin.resources.point_rules.fields.percentage'))
                            ->numeric()
                            ->minValue(0.01)
                            ->maxValue(999.99)
                            ->suffix('%')
                            ->required(fn ($get): bool => $get('type') === PointRuleType::Percentage->value)
                            ->visible(fn ($get): bool => $get('type') === PointRuleType::Percentage->value),
                        TextInput::make('base_amount')
                            ->label(__('admin.resources.point_rules.fields.base_amount'))
                            ->numeric()
                            ->minValue(0.01)
                            ->prefix('QAR')
                            ->required(fn ($get): bool => $get('type') === PointRuleType::Percentage->value)
                            ->visible(fn ($get): bool => $get('type') === PointRuleType::Percentage->value),
                    ]),
                Section::make(__('admin.resources.point_rules.sections.scope'))
                    ->description(__('admin.resources.point_rules.descriptions.scope'))
                    ->icon(Heroicon::OutlinedFlag)
                    ->iconColor('primary')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('team_id')
                            ->label(__('admin.common.team'))
                            ->options(fn (): array => Team::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->placeholder(__('admin.common.all_teams')),
                        Select::make('season_id')
                            ->label(__('admin.common.season'))
                            ->options(fn (): array => Season::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->placeholder(__('admin.common.all_seasons')),
                        TextInput::make('priority')
                            ->label(__('admin.resources.point_rules.fields.priority'))
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->label(__('admin.resources.point_rules.fields.is_active'))
                            ->default(true),
                        DateTimePicker::make('starts_at')
                            ->label(__('admin.resources.point_rules.fields.starts_at'))
                            ->seconds(false)
                            ->prefixIcon(Heroicon::OutlinedCalendar),
                        DateTimePicker::make('ends_at')
                            ->label(__('admin.resources.point_rules.fields.ends_at'))
                            ->seconds(false)
                            ->after('starts_at')
                            ->prefixIcon(Heroicon::OutlinedCalendar),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.resources.point_rules.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('admin.resources.point_rules.fields.type'))
                    ->badge(),
                TextColumn::make('details')
                    ->label(__('admin.resources.point_rules.fields.value'))
                    ->getStateUsing(fn (PointRule $record): string => $record->type === PointRuleType::Percentage
                        ? __('admin.resources.point_rules.messages.percentage_value', ['percentage' => $record->percentage, 'base_amount' => $record->base_amount, 'points' => $record->pointsValue()])
                        : __('admin.resources.point_rules.messages.points_value', ['points' => $record->pointsValue()])),
                TextColumn::make('team.name')
                    ->label(__('admin.resources.point_rules.fields.scope_team'))
                    ->placeholder(__('admin.common.all')),
                TextColumn::make('season.name')
                    ->label(__('admin.resources.point_rules.fields.scope_season'))
                    ->placeholder(__('admin.common.all')),
                IconColumn::make('is_active')
                    ->label(__('admin.resources.point_rules.fields.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label(__('admin.resources.point_rules.fields.starts_at'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder(__('admin.common.always')),
                TextColumn::make('ends_at')
                    ->label(__('admin.resources.point_rules.fields.ends_at'))
                    ->dateTime()
                    ->sortable()
                    ->placeholder(__('admin.common.always')),
                TextColumn::make('priority')
                    ->label(__('admin.resources.point_rules.fields.priority'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('admin.resources.point_rules.fields.type'))
                    ->options(EnumOptions::for(PointRuleType::class)),
                Filter::make('is_active')
                    ->query(fn (Builder $q) => $q->where('is_active', true))
                    ->label(__('admin.resources.point_rules.filters.active_only')),
                SelectFilter::make('team_id')
                    ->label(__('admin.common.team'))
                    ->options(fn (): array => Team::query()->orderBy('name')->pluck('name', 'id')->all()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => PointRules\ListPointRules::route('/'),
            'create' => PointRules\CreatePointRule::route('/create'),
            'edit' => PointRules\EditPointRule::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.point_rules.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.point_rules.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.point_rules.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.groups.loyalty');
    }
}
