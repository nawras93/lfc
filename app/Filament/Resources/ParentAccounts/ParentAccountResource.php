<?php

namespace App\Filament\Resources\ParentAccounts;

use App\Filament\Resources\ParentAccounts\Pages\CreateParentAccount;
use App\Filament\Resources\ParentAccounts\Pages\EditParentAccount;
use App\Filament\Resources\ParentAccounts\Pages\ListParentAccounts;
use App\Models\Candidate;
use App\Models\ParentAccount;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ParentAccountResource extends Resource
{
    protected static ?string $model = ParentAccount::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|\UnitEnum|null $navigationGroup = 'Accounts';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Parent account')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('whatsapp')
                            ->tel()
                            ->maxLength(255),
                        Select::make('player_ids')
                            ->label('Linked players')
                            ->options(fn (): array => Candidate::query()
                                ->where('is_player', true)
                                ->orderBy('full_name')
                                ->pluck('full_name', 'id')
                                ->all())
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Only candidates already marked as players can be linked.'),
                        Placeholder::make('account_status')
                            ->label('Status')
                            ->content(fn (?ParentAccount $record): string => $record?->accepted_at
                                ? 'Accepted'
                                : ($record?->invited_at ? 'Invited' : 'Draft')),
                        DateTimePicker::make('invited_at')
                            ->seconds(false),
                        DateTimePicker::make('accepted_at')
                            ->seconds(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('players_count')
                    ->counts('players')
                    ->label('Players'),
                TextColumn::make('invited_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('accepted_at')
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

    /**
     * @param  array<int, int|string>  $playerIds
     */
    public static function syncPlayers(ParentAccount $parentAccount, array $playerIds): void
    {
        $playerIds = array_values(array_unique(array_map('intval', $playerIds)));

        $parentAccount->syncPlayers($playerIds);
    }
}
