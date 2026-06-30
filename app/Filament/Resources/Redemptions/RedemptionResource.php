<?php

namespace App\Filament\Resources\Redemptions;

use App\Filament\Resources\Redemptions\Pages\ListRedemptions;
use App\Models\Redemption;
use BackedEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RedemptionResource extends Resource
{
    protected static ?string $model = Redemption::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string|\UnitEnum|null $navigationGroup = 'Rewards';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Redemption')
                    ->columns(2)
                    ->schema([
                        TextInput::make('voucher_code'),
                        Select::make('status')
                            ->options([
                                'issued' => 'Issued',
                                'fulfilled' => 'Fulfilled',
                                'cancelled' => 'Cancelled',
                            ]),
                        DateTimePicker::make('fulfilled_at'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label('Account')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('player.full_name')
                    ->label('Player')
                    ->formatStateUsing(fn (?string $state): string => $state ?? '—'),
                TextColumn::make('item.name')
                    ->label('Item')
                    ->sortable(),
                TextColumn::make('points_spent')
                    ->sortable(),
                TextColumn::make('voucher_code')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Voucher code copied'),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('fulfilled_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Redeemed at'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRedemptions::route('/'),
        ];
    }
}
