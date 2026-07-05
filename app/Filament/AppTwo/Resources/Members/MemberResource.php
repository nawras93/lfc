<?php

namespace App\Filament\AppTwo\Resources\Members;

use App\Enums\AccountType;
use App\Filament\AppTwo\Resources\Members\Pages\ListMembers;
use App\Filament\AppTwo\Resources\Members\Pages\ViewMember;
use App\Filament\AppTwo\Resources\Members\RelationManagers\DiscountTransactionsRelationManager;
use App\Models\ParentAccount;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MemberResource extends Resource
{
    protected static ?string $model = ParentAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.members.sections.member'))
                    ->description(__('admin.resources.members.descriptions.member'))
                    ->icon(Heroicon::OutlinedUser)
                    ->iconColor('primary')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('admin.resources.members.fields.name')),
                        TextEntry::make('email')
                            ->label(__('admin.resources.members.fields.email'))
                            ->copyable(),
                        TextEntry::make('phone')
                            ->label(__('admin.resources.members.fields.phone'))
                            ->placeholder(__('admin.common.not_available')),
                        TextEntry::make('accepted_at')
                            ->label(__('admin.resources.members.fields.registered_at'))
                            ->dateTime(),
                        TextEntry::make('app')
                            ->label(__('admin.resources.members.fields.app'))
                            ->badge(),
                        TextEntry::make('account_type')
                            ->label(__('admin.resources.members.fields.account_type'))
                            ->badge(),
                        TextEntry::make('discount_percent')
                            ->label(__('admin.resources.members.fields.discount_percent'))
                            ->state(fn (ParentAccount $record): string => number_format($record->discountPercent(), 1).'%')
                            ->badge()
                            ->color('success'),
                        TextEntry::make('discount_cap_percent')
                            ->label(__('admin.resources.members.fields.discount_cap_percent'))
                            ->state(fn (): string => number_format(config('loyalty.app_two.discount_cap_bp') / 100, 0).'%')
                            ->badge()
                            ->color('warning'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.resources.members.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('admin.resources.members.fields.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('admin.resources.members.fields.phone'))
                    ->searchable(),
                TextColumn::make('discount_percent')
                    ->label(__('admin.resources.members.fields.discount_percent'))
                    ->state(fn (ParentAccount $record): string => number_format($record->discountPercent(), 1).'%')
                    ->badge()
                    ->color('success'),
                TextColumn::make('accepted_at')
                    ->label(__('admin.resources.members.fields.registered_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('accepted_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DiscountTransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembers::route('/'),
            'view' => ViewMember::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('account_type', AccountType::Member->value);
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.members.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.members.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.members.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.groups.accounts');
    }
}
