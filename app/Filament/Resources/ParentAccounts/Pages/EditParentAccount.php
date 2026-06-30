<?php

namespace App\Filament\Resources\ParentAccounts\Pages;

use App\Filament\Resources\ParentAccounts\ParentAccountResource;
use App\Models\ParentAccount;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditParentAccount extends EditRecord
{
    protected static string $resource = ParentAccountResource::class;

    /** @var array<int, int|string> */
    protected array $playerIds = [];

    protected function getHeaderActions(): array
    {
        return [
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
