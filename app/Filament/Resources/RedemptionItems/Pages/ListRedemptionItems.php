<?php

namespace App\Filament\Resources\RedemptionItems\Pages;

use App\Filament\Resources\RedemptionItems\RedemptionItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRedemptionItems extends ListRecords
{
    protected static string $resource = RedemptionItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
