<?php

namespace App\Filament\Resources\Offers;

use App\Enums\OfferAudience;
use App\Filament\Resources\Offers\Pages\CreateOffer;
use App\Filament\Resources\Offers\Pages\EditOffer;
use App\Filament\Resources\Offers\Pages\ListOffers;
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

    protected static string|\UnitEnum|null $navigationGroup = 'Rewards';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Offer details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('body')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Radio::make('audience')
                            ->required()
                            ->options(OfferAudience::class),
                        Toggle::make('is_published'),
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
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('audience')
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_published')
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
            'index' => ListOffers::route('/'),
            'create' => CreateOffer::route('/create'),
            'edit' => EditOffer::route('/{record}/edit'),
        ];
    }
}
