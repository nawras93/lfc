<?php

namespace App\Filament\Resources\ParentAccounts\Pages;

use App\Filament\Resources\ParentAccounts\ParentAccountResource;
use App\Models\ParentAccount;
use App\Services\PointsEngine;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditParentAccount extends EditRecord
{
    protected static string $resource = ParentAccountResource::class;

    /** @var array<int, int|string> */
    protected array $playerIds = [];

    protected function getHeaderActions(): array
    {
        return [
            Action::make('grantPoints')
                ->label('Grant points')
                ->icon('heroicon-o-currency-dollar')
                ->visible(fn (ParentAccount $record): bool => $record->isVvipClient() && auth()->user()?->hasRole('Admin'))
                ->form([
                    TextInput::make('points')
                        ->required()
                        ->numeric()
                        ->minValue(1)
                        ->label('Points to grant'),
                    TextInput::make('reason')
                        ->required()
                        ->maxLength(255)
                        ->label('Reason'),
                ])
                ->action(function (array $data, ParentAccount $record, PointsEngine $engine): void {
                    $engine->grantToAccount(
                        $record,
                        (int) $data['points'],
                        $data['reason'],
                        auth()->user(),
                    );

                    Notification::make()
                        ->title('Points granted successfully')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var ParentAccount $record */
        $record = $this->record;

        $data['player_ids'] = $record->players()->pluck('candidates.id')->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->playerIds = $data['player_ids'] ?? [];

        unset($data['player_ids']);

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var ParentAccount $record */
        $record = $this->record;

        ParentAccountResource::syncPlayers($record, $this->playerIds);
    }
}
