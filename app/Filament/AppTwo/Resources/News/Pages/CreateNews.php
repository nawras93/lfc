<?php

namespace App\Filament\AppTwo\Resources\News\Pages;

use App\Filament\AppTwo\Resources\News\NewsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNews extends CreateRecord
{
    protected static string $resource = NewsResource::class;
}
