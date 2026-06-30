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

    protected static string|\UnitEnum|null $navigationGroup = 'Loyalty';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rule')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Radio::make('type')
                            ->options(EnumOptions::for(PointRuleType::class))
                            ->required()
                            ->live(),
                        TextInput::make('points')
                            ->numeric()
                            ->minValue(1)
                            ->required(fn ($get): bool => $get('type') === PointRuleType::Fixed->value)
                            ->visible(fn ($get): bool => $get('type') === PointRuleType::Fixed->value),
                        TextInput::make('percentage')
                            ->numeric()
                            ->minValue(0.01)
                            ->maxValue(999.99)
                            ->required(fn ($get): bool => $get('type') === PointRuleType::Percentage->value)
                            ->visible(fn ($get): bool => $get('type') === PointRuleType::Percentage->value),
                        TextInput::make('base_amount')
                            ->numeric()
                            ->minValue(0.01)
                            ->required(fn ($get): bool => $get('type') === PointRuleType::Percentage->value)
                            ->visible(fn ($get): bool => $get('type') === PointRuleType::Percentage->value),
                        Select::make('team_id')
                            ->label('Team')
                            ->options(fn (): array => Team::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->placeholder('All teams'),
                        Select::make('season_id')
                            ->label('Season')
                            ->options(fn (): array => Season::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->placeholder('All seasons'),
                        TextInput::make('priority')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_active')
                            ->default(true),
                        DateTimePicker::make('starts_at')
                            ->seconds(false),
                        DateTimePicker::make('ends_at')
                            ->seconds(false)
                            ->after('starts_at'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('details')
                    ->label('Value')
                    ->getStateUsing(fn (PointRule $record): string => $record->type === PointRuleType::Percentage
                        ? "{$record->percentage}% × {$record->base_amount} = {$record->pointsValue()} pts"
                        : "{$record->pointsValue()} pts"),
                TextColumn::make('team.name')
                    ->label('Scope (team)')
                    ->placeholder('All'),
                TextColumn::make('season.name')
                    ->label('Scope (season)')
                    ->placeholder('All'),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Always'),
                TextColumn::make('ends_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Always'),
                TextColumn::make('priority')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(EnumOptions::for(PointRuleType::class)),
                Filter::make('is_active')
                    ->query(fn (Builder $q) => $q->where('is_active', true))
                    ->label('Active only'),
                SelectFilter::make('team_id')
                    ->label('Team')
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
}
