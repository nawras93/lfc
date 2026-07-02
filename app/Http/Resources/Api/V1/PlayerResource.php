<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'playing_position' => $this->playingPositionLabel(),
            'team_name' => $this->team?->localized('name'),
            'points_balance' => $this->pointsBalance(),
            'is_player' => $this->is_player,
            'progress' => $this->publicProgressLabel(),
        ];
    }
}
