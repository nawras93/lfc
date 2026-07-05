<?php

namespace App\Filament\AppTwo\Resources\VvipMembers;

use App\Enums\AccountType;
use App\Filament\AppTwo\Resources\VvipMembers\Pages\CreateVvipMember;
use App\Filament\AppTwo\Resources\VvipMembers\Pages\EditVvipMember;
use App\Filament\AppTwo\Resources\VvipMembers\Pages\ListVvipMembers;
use App\Models\MembershipTier;
use App\Models\ParentAccount;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VvipMemberResource extends Resource
{
    protected static ?string $model = ParentAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.vvip_members.sections.account'))
                    ->description(__('admin.resources.vvip_members.descriptions.account'))
                    ->icon(Heroicon::OutlinedStar)
                    ->iconColor('primary')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('admin.resources.vvip_members.fields.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('admin.resources.vvip_members.fields.email'))
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('password')
                            ->label(__('admin.resources.vvip_members.fields.password'))
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('admin.resources.vvip_members.fields.phone'))
                            ->tel()
                            ->maxLength(255),
                        Select::make('membership_tier_id')
                            ->label(__('admin.resources.vvip_members.fields.membership_tier'))
                            ->options(fn (): array => MembershipTier::query()
                                ->orderByDesc('level')
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (MembershipTier $tier): array => [
                                    $tier->id => sprintf('%s (%d)', $tier->localized('name') ?? $tier->name, $tier->level),
                                ])
                                ->all())
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('member_number')
                            ->label(__('admin.resources.vvip_members.fields.member_number'))
                            ->required()
                            ->maxLength(255),
                        DatePicker::make('membership_valid_until')
                            ->label(__('admin.resources.vvip_members.fields.membership_valid_until'))
                            ->required()
                            ->native(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.resources.vvip_members.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('admin.resources.vvip_members.fields.email'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('membershipTier.name')
                    ->label(__('admin.resources.vvip_members.fields.membership_tier'))
                    ->state(fn (ParentAccount $record): ?string => $record->membershipTier?->localized('name'))
                    ->sortable(),
                TextColumn::make('member_number')
                    ->label(__('admin.resources.vvip_members.fields.member_number'))
                    ->searchable(),
                TextColumn::make('membership_valid_until')
                    ->label(__('admin.resources.vvip_members.fields.membership_valid_until'))
                    ->date()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVvipMembers::route('/'),
            'create' => CreateVvipMember::route('/create'),
            'edit' => EditVvipMember::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('account_type', AccountType::VvipMember->value);
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.vvip_members.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.vvip_members.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.vvip_members.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.groups.membership');
    }
}
