<?php

namespace App\Filament\Resources\Redemptions;

use App\Enums\RedemptionStatus;
use App\Filament\Resources\Redemptions\Pages\ListRedemptions;
use App\Models\Redemption;
use App\Services\RedemptionService;
use App\Support\EnumOptions;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RedemptionResource extends Resource
{
    protected static ?string $model = Redemption::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.redemptions.sections.redemption'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('voucher_code')
                            ->label(__('admin.resources.redemptions.fields.voucher_code')),
                        Select::make('status')
                            ->label(__('admin.resources.redemptions.fields.status'))
                            ->options(EnumOptions::for(RedemptionStatus::class)),
                        DateTimePicker::make('fulfilled_at')
                            ->label(__('admin.resources.redemptions.fields.fulfilled_at')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('admin.resources.redemptions.fields.id'))
                    ->sortable(),
                TextColumn::make('parent.name')
                    ->label(__('admin.common.account'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('player.full_name')
                    ->label(__('admin.common.player'))
                    ->formatStateUsing(fn (?string $state): string => $state ?? __('admin.common.not_available')),
                TextColumn::make('item.name')
                    ->label(__('admin.common.item'))
                    ->sortable(),
                TextColumn::make('points_spent')
                    ->label(__('admin.resources.redemptions.fields.points_spent'))
                    ->sortable(),
                TextColumn::make('voucher_code')
                    ->label(__('admin.resources.redemptions.fields.voucher_code'))
                    ->searchable()
                    ->copyable()
                    ->copyMessage(__('admin.common.voucher_code_copied')),
                TextColumn::make('status')
                    ->label(__('admin.resources.redemptions.fields.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('fulfilled_at')
                    ->label(__('admin.resources.redemptions.fields.fulfilled_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label(__('admin.resources.redemptions.fields.redeemed_at')),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('admin.resources.redemptions.fields.status'))
                    ->options(EnumOptions::for(RedemptionStatus::class)),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                self::markFulfilledAction(),
            ]);
    }

    public static function markFulfilledAction(): Action
    {
        return Action::make('markFulfilled')
            ->label(__('admin.resources.redemptions.actions.mark_fulfilled'))
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading(__('admin.resources.redemptions.messages.mark_fulfilled_heading'))
            ->modalDescription(__('admin.resources.redemptions.messages.mark_fulfilled_description'))
            ->visible(fn (Redemption $record): bool => $record->status === RedemptionStatus::Issued
                && (auth()->user()?->hasRole(['Admin', 'Management']) ?? false))
            ->action(function (Redemption $record): void {
                app(RedemptionService::class)->fulfill($record, auth()->user());

                Notification::make()
                    ->success()
                    ->title(__('admin.resources.redemptions.messages.fulfilled_title'))
                    ->body(__('admin.resources.redemptions.messages.fulfilled_body', ['code' => $record->voucher_code]))
                    ->send();
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRedemptions::route('/'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.redemptions.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.redemptions.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.redemptions.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.groups.rewards');
    }
}
