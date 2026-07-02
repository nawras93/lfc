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

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.redemption_items.sections.details'))
                    ->description(__('admin.resources.redemption_items.descriptions.details'))
                    ->icon(Heroicon::OutlinedGift)
                    ->iconColor('primary')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('admin.resources.redemption_items.fields.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('name_ar')
                            ->label(__('admin.resources.redemption_items.fields.name_ar'))
                            ->maxLength(255)
                            ->hint(__('admin.common.arabic'))
                            ->extraInputAttributes(['dir' => 'rtl']),
                        Select::make('type')
                            ->label(__('admin.resources.redemption_items.fields.type'))
                            ->required()
                            ->native(false)
                            ->options(RedemptionType::class)
                            ->columnSpanFull(),
                        Textarea::make('description')
                            ->label(__('admin.resources.redemption_items.fields.description'))
                            ->maxLength(65535)
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('description_ar')
                            ->label(__('admin.resources.redemption_items.fields.description_ar'))
                            ->maxLength(65535)
                            ->rows(3)
                            ->hint(__('admin.common.arabic'))
                            ->extraInputAttributes(['dir' => 'rtl'])
                            ->columnSpanFull(),
                    ]),
                Section::make(__('admin.resources.redemption_items.sections.availability'))
                    ->description(__('admin.resources.redemption_items.descriptions.availability'))
                    ->icon(Heroicon::OutlinedArchiveBox)
                    ->iconColor('primary')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('points_cost')
                            ->label(__('admin.resources.redemption_items.fields.points_cost'))
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->prefixIcon(Heroicon::OutlinedStar)
                            ->suffix(__('admin.common.points')),
                        TextInput::make('stock')
                            ->label(__('admin.resources.redemption_items.fields.stock'))
                            ->numeric()
                            ->minValue(0)
                            ->nullable()
                            ->placeholder(__('admin.common.unlimited'))
                            ->helperText(__('admin.resources.redemption_items.helper.stock')),
                        Toggle::make('is_active')
                            ->label(__('admin.resources.redemption_items.fields.is_active'))
                            ->default(true)
                            ->columnSpanFull(),
                        DateTimePicker::make('valid_from')
                            ->label(__('admin.resources.redemption_items.fields.valid_from'))
                            ->seconds(false)
                            ->prefixIcon(Heroicon::OutlinedCalendar),
                        DateTimePicker::make('valid_until')
                            ->label(__('admin.resources.redemption_items.fields.valid_until'))
                            ->seconds(false)
                            ->after('valid_from')
                            ->prefixIcon(Heroicon::OutlinedCalendar),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.resources.redemption_items.fields.name'))
                    ->state(fn (RedemptionItem $record): ?string => $record->localized('name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('admin.resources.redemption_items.fields.type'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('points_cost')
                    ->label(__('admin.resources.redemption_items.fields.points_cost'))
                    ->sortable(),
                TextColumn::make('stock')
                    ->label(__('admin.resources.redemption_items.fields.stock'))
                    ->formatStateUsing(fn (?int $state): string => $state === null ? __('admin.common.unlimited') : (string) $state)
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('admin.resources.redemption_items.fields.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('valid_from')
                    ->label(__('admin.resources.redemption_items.fields.valid_from'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('valid_until')
                    ->label(__('admin.resources.redemption_items.fields.valid_until'))
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

    public static function getModelLabel(): string
    {
        return __('admin.resources.redemption_items.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.redemption_items.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.redemption_items.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.groups.rewards');
    }
}
