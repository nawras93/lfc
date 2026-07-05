<?php

namespace App\Filament\AppTwo\Resources\Offers\Pages;

use App\Filament\AppTwo\Resources\Offers\OfferResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOffer extends CreateRecord
{
    protected static string $resource = OfferResource::class;
}
