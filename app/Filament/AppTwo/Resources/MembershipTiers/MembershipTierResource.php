<?php

namespace App\Filament\AppTwo\Resources\MembershipTiers;

use App\Filament\AppTwo\Resources\MembershipTiers\Pages\CreateMembershipTier;
use App\Filament\AppTwo\Resources\MembershipTiers\Pages\EditMembershipTier;
use App\Filament\AppTwo\Resources\MembershipTiers\Pages\ListMembershipTiers;
use App\Filament\AppTwo\Resources\MembershipTiers\RelationManagers\BenefitsRelationManager;
use App\Models\MembershipTier;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembershipTierResource extends Resource
{
    protected static ?string $model = MembershipTier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('admin.resources.membership_tiers.sections.tier'))
                    ->description(__('admin.resources.membership_tiers.descriptions.tier'))
                    ->icon(Heroicon::OutlinedIdentification)
                    ->iconColor('primary')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('admin.resources.membership_tiers.fields.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('name_ar')
                            ->label(__('admin.resources.membership_tiers.fields.name_ar'))
                            ->maxLength(255)
                            ->hint(__('admin.common.arabic'))
                            ->extraInputAttributes(['dir' => 'rtl']),
                        TextInput::make('level')
                            ->label(__('admin.resources.membership_tiers.fields.level'))
                            ->required()
                            ->numeric()
                            ->minValue(1),
                        ColorPicker::make('accent_color')
                            ->label(__('admin.resources.membership_tiers.fields.accent_color')),
                        Toggle::make('is_active')
                            ->label(__('admin.resources.membership_tiers.fields.is_active'))
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('admin.resources.membership_tiers.fields.name'))
                    ->state(fn (MembershipTier $record): ?string => $record->localized('name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('level')
                    ->label(__('admin.resources.membership_tiers.fields.level'))
                    ->sortable(),
                TextColumn::make('accent_color')
                    ->label(__('admin.resources.membership_tiers.fields.accent_color'))
                    ->badge()
                    ->color('warning'),
                IconColumn::make('is_active')
                    ->label(__('admin.resources.membership_tiers.fields.is_active'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('benefits_count')
                    ->counts('benefits')
                    ->label(__('admin.resources.membership_tiers.fields.benefits_count')),
            ])
            ->defaultSort('level', 'desc')
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            BenefitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembershipTiers::route('/'),
            'create' => CreateMembershipTier::route('/create'),
            'edit' => EditMembershipTier::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('admin.resources.membership_tiers.singular');
    }

    public static function getPluralModelLabel(): string
    {
        return __('admin.resources.membership_tiers.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.resources.membership_tiers.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.nav.groups.membership');
    }
}
