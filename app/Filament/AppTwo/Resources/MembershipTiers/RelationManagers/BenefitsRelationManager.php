<?php

namespace App\Filament\AppTwo\Resources\MembershipTiers\RelationManagers;

use App\Models\MembershipBenefit;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BenefitsRelationManager extends RelationManager
{
    protected static string $relationship = 'benefits';

    protected static ?string $title = null;

    public static function getTitle($ownerRecord, string $pageClass): string
    {
        return __('admin.resources.membership_tiers.relations.benefits');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(__('admin.resources.membership_benefits.fields.title'))
                    ->required()
                    ->maxLength(255),
                TextInput::make('title_ar')
                    ->label(__('admin.resources.membership_benefits.fields.title_ar'))
                    ->maxLength(255)
                    ->hint(__('admin.common.arabic'))
                    ->extraInputAttributes(['dir' => 'rtl']),
                Textarea::make('description')
                    ->label(__('admin.resources.membership_benefits.fields.description'))
                    ->rows(3)
                    ->columnSpanFull(),
                Textarea::make('description_ar')
                    ->label(__('admin.resources.membership_benefits.fields.description_ar'))
                    ->rows(3)
                    ->hint(__('admin.common.arabic'))
                    ->extraInputAttributes(['dir' => 'rtl'])
                    ->columnSpanFull(),
                TextInput::make('icon')
                    ->label(__('admin.resources.membership_benefits.fields.icon'))
                    ->maxLength(255),
                TextInput::make('sort_order')
                    ->label(__('admin.resources.membership_benefits.fields.sort_order'))
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label(__('admin.resources.membership_benefits.fields.title'))
                    ->state(fn (MembershipBenefit $record): ?string => $record->localized('title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('icon')
                    ->label(__('admin.resources.membership_benefits.fields.icon')),
                TextColumn::make('sort_order')
                    ->label(__('admin.resources.membership_benefits.fields.sort_order'))
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('admin.common.updated_at'))
                    ->dateTime()
                    ->toggleable(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
