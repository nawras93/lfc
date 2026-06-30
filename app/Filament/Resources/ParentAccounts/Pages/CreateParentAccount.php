<?php

namespace App\Filament\Resources\ParentAccounts\Pages;

use App\Filament\Resources\ParentAccounts\ParentAccountResource;
use App\Models\ParentAccount;
use Filament\Resources\Pages\CreateRecord;

class CreateParentAccount extends CreateRecord
{
    protected static string $resource = ParentAccountResource::class;

    /** @var array<int, int|string> */
    protected array $playerIds = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->playerIds = $data['player_ids'] ?? [];

        unset($data['player_ids']);

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var ParentAccount $record */
        $record = $this->record;

        ParentAccountResource::syncPlayers($record, $this->playerIds);
    }
}
