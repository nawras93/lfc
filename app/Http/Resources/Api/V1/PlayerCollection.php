<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PlayerCollection extends ResourceCollection
{
    public $collects = PlayerResource::class;

    public function toArray(Request $request): array
    {
        return $this->collection->all();
    }
}
