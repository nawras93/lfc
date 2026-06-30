<?php

namespace App\Filament\Resources\ParentAccounts\Pages;

use App\Filament\Resources\ParentAccounts\ParentAccountResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListParentAccounts extends ListRecords
{
    protected static string $resource = ParentAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
