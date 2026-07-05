<?php

namespace App\Filament\AppTwo\Resources\Offers;

use App\Enums\OfferAudience;
use App\Filament\AppTwo\Resources\Offers\Pages\CreateOffer;
use App\Filament\AppTwo\Resources\Offers\Pages\EditOffer;
use App\Filament\AppTwo\Resources\Offers\Pages\ListOffers;
use App\Models\Offer;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OfferResource extends Resource
{
    protected static ?string $model = Offer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.offers.sections.content'))
                    ->description(__('admin.resources.offers.descriptions.content'))
                    ->icon(Heroicon::OutlinedMegaphone)
                    ->iconColor('primary')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label(__('admin.resources.offers.fields.title'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('title_ar')
                            ->label(__('admin.resources.offers.fields.title_ar'))
                            ->maxLength(255)
                            ->hint(__('admin.common.arabic'))
                            ->extraInputAttributes(['dir' => 'rtl']),
                        Textarea::make('body')
                            ->label(__('admin.resources.offers.fields.body'))
                            ->required()
                            ->maxLength(65535)
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('body_ar')
                            ->label(__('admin.resources.offers.fields.body_ar'))
                            ->maxLength(65535)
                            ->rows(4)
                            ->hint(__('admin.common.arabic'))
                            ->extraInputAttributes(['dir' => 'rtl'])
                            ->columnSpanFull(),
                    ]),
                Section::make(__('admin.resources.offers.sections.schedule'))
                    ->description(__('admin.resources.offers.descriptions.schedule'))
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->iconColor('primary')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Radio::make('audience')
                            ->label(__('admin.resources.offers.fields.audience'))
                            ->required()
                            ->options(OfferAudience::class)
                            ->columnSpanFull(),
                        Toggle::make('is_published')
                            ->label(__('admin.resources.offers.fields.is_published'))
                            ->columnSpanFull(),
                        DateTimePicker::make('valid_from')
                            ->label(__('admin.resources.offers.fields.valid_from'))
                            ->seconds(false)
                            ->prefixIcon(Heroicon::OutlinedCalendar),
                        DateTimePicker::make('valid_until')
                            ->label(__('admin.resources.offers.fields.valid_until'))
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
                TextColumn::make('title')
                    ->label(__('admin.resources.offers.fields.title'))
                    ->state(fn (Offer $record): ?string => $record->localized('title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('audience')
                    ->label(__('admin.resources.offers.fields.audience'))
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_published')
                    ->label(__('admin.resources.offers.fields.is_published'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('valid_from')
                    ->label(__('admin.resources.offers.fields.valid_from'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('valid_until')
                    ->label(__('admin.resources.offers.fields.valid_until'))
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
            'index' => ListOffers::route('/'),
            'create' => CreateOffer::route('/create'),
            'edit' => EditOffer::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.offers.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.offers.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.offers.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.groups.content');
    }
}
