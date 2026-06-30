<?php

namespace App\Filament\Resources\RedemptionItems;

use App\Enums\RedemptionType;
use App\Filament\Resources\RedemptionItems\Pages\CreateRedemptionItem;
use App\Filament\Resources\RedemptionItems\Pages\EditRedemptionItem;
use App\Filament\Resources\RedemptionItems\Pages\ListRedemptionItems;
use App\Models\RedemptionItem;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class RedemptionItemResource extends Resource
{
    protected static ?string $model = RedemptionItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static string|\UnitEnum|null $navigationGroup = 'Rewards';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Item details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->required()
                            ->options(RedemptionType::class),
                        Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        TextInput::make('points_cost')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('stock')
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->helperText('Leave empty for unlimited stock.'),
                        Toggle::make('is_active')
                            ->default(true),
                        DateTimePicker::make('valid_from')
                            ->seconds(false),
                        DateTimePicker::make('valid_until')
                            ->seconds(false)
                            ->after('valid_from'),
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
                    ->badge()
                    ->sortable(),
                TextColumn::make('points_cost')
                    ->sortable(),
                TextColumn::make('stock')
                    ->formatStateUsing(fn (?int $state): string => $state === null ? 'Unlimited' : (string) $state)
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('valid_from')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('valid_until')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRedemptionItems::route('/'),
            'create' => CreateRedemptionItem::route('/create'),
            'edit' => EditRedemptionItem::route('/{record}/edit'),
        ];
    }
}
