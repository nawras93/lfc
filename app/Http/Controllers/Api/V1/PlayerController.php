<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PlayerCollection;
use App\Http\Resources\Api\V1\PlayerResource;
use App\Models\Candidate;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    public function index(Request $request): PlayerCollection
    {
        $players = $request->user()
            ->players()
            ->with('team')
            ->orderBy('full_name')
            ->get();

        return new PlayerCollection($players);
    }

    public function show(Request $request, Candidate $player): PlayerResource
    {
        $scopedPlayer = $request->user()
            ->players()
            ->with('team')
            ->whereKey($player->getKey())
            ->firstOrFail();

        return PlayerResource::make($scopedPlayer);
    }
}
