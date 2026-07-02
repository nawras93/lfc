<?php

namespace App\Filament\Resources\ParentAccounts;

use App\Filament\Resources\ParentAccounts\Pages\CreateParentAccount;
use App\Filament\Resources\ParentAccounts\Pages\EditParentAccount;
use App\Filament\Resources\ParentAccounts\Pages\ListParentAccounts;
use App\Enums\AccountType;
use App\Models\Candidate;
use App\Models\ParentAccount;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ParentAccountResource extends Resource
{
    protected static ?string $model = ParentAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.parent_accounts.sections.contact'))
                    ->description(__('admin.resources.parent_accounts.descriptions.contact'))
                    ->icon(Heroicon::OutlinedUser)
                    ->iconColor('primary')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('admin.resources.parent_accounts.fields.name'))
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon(Heroicon::OutlinedUser),
                        TextInput::make('email')
                            ->label(__('admin.resources.parent_accounts.fields.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->prefixIcon(Heroicon::OutlinedEnvelope),
                        TextInput::make('phone')
                            ->label(__('admin.resources.parent_accounts.fields.phone'))
                            ->tel()
                            ->maxLength(255)
                            ->prefixIcon(Heroicon::OutlinedPhone),
                        TextInput::make('whatsapp')
                            ->label(__('admin.resources.parent_accounts.fields.whatsapp'))
                            ->tel()
                            ->maxLength(255)
                            ->prefixIcon(Heroicon::OutlinedChatBubbleLeftRight),
                    ]),
                Section::make(__('admin.resources.parent_accounts.sections.membership'))
                    ->description(__('admin.resources.parent_accounts.descriptions.membership'))
                    ->icon(Heroicon::OutlinedIdentification)
                    ->iconColor('primary')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('account_type')
                            ->label(__('admin.resources.parent_accounts.fields.account_type'))
                            ->options(AccountType::class)
                            ->native(false)
                            ->default(AccountType::Parent->value)
                            ->afterStateUpdated(function ($set, $state): void {
                                if ($state === AccountType::VvipClient->value) {
                                    $set('is_vvip', true);
                                }
                            })
                            ->live(),
                        Toggle::make('is_vvip')
                            ->label(__('admin.resources.parent_accounts.fields.is_vvip'))
                            ->visible(fn (): bool => auth()->user()?->hasRole('Admin') ?? false),
                        Select::make('player_ids')
                            ->label(__('admin.resources.parent_accounts.fields.linked_players'))
                            ->options(fn (): array => Candidate::query()
                                ->where('is_player', true)
                                ->orderBy('full_name')
                                ->pluck('full_name', 'id')
                                ->all())
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText(__('admin.resources.parent_accounts.helper.linked_players'))
                            ->visible(fn (?ParentAccount $record, $get): bool => $get('account_type') !== AccountType::VvipClient->value && ($record === null || ! $record->isVvipClient()))
                            ->columnSpanFull(),
                        Placeholder::make('account_status')
                            ->label(__('admin.resources.parent_accounts.fields.account_status'))
                            ->content(fn (?ParentAccount $record): string => $record?->accepted_at
                                ? __('admin.resources.parent_accounts.status.accepted')
                                : ($record?->invited_at ? __('admin.resources.parent_accounts.status.invited') : __('admin.resources.parent_accounts.status.draft'))),
                        Placeholder::make('balance')
                            ->label(__('admin.resources.parent_accounts.fields.balance'))
                            ->content(fn (?ParentAccount $record): string => $record ? (string) $record->pointsBalance() : '0'),
                        DateTimePicker::make('invited_at')
                            ->label(__('admin.resources.parent_accounts.fields.invited_at'))
                            ->seconds(false)
                            ->prefixIcon(Heroicon::OutlinedCalendar),
                        DateTimePicker::make('accepted_at')
                            ->label(__('admin.resources.parent_accounts.fields.accepted_at'))
                            ->seconds(false)
                            ->prefixIcon(Heroicon::OutlinedCalendar),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.resources.parent_accounts.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('admin.resources.parent_accounts.fields.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('account_type')
                    ->label(__('admin.resources.parent_accounts.fields.account_type'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('players_count')
                    ->counts('players')
                    ->label(__('admin.resources.parent_accounts.fields.players_count')),
                IconColumn::make('is_vvip')
                    ->boolean()
                    ->label(__('admin.resources.parent_accounts.fields.is_vvip'))
                    ->sortable(),
                TextColumn::make('invited_at')
                    ->label(__('admin.resources.parent_accounts.fields.invited_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('accepted_at')
                    ->label(__('admin.resources.parent_accounts.fields.accepted_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListParentAccounts::route('/'),
            'create' => CreateParentAccount::route('/create'),
            'edit' => EditParentAccount::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.parent_accounts.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.parent_accounts.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.parent_accounts.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.groups.accounts');
    }

    /**
     * @param  array<int, int|string>  $playerIds
     */
    public static function syncPlayers(ParentAccount $parentAccount, array $playerIds): void
    {
        $playerIds = array_values(array_unique(array_map('intval', $playerIds)));

        $parentAccount->syncPlayers($playerIds);
    }
}
