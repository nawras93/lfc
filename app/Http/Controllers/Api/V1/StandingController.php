<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Standing;
use Illuminate\Http\JsonResponse;

class StandingController extends Controller
{
    public function index(): JsonResponse
    {
        $standings = Standing::query()
            ->orderByDesc('points')
            ->orderByRaw('(goals_for - goals_against) desc')
            ->orderByDesc('goals_for')
            ->get()
            ->values()
            ->map(fn (Standing $standing, int $index) => [
                'id' => $standing->id,
                'position' => $index + 1,
                'club_name' => $standing->localized('club_name'),
                'played' => $standing->played,
                'won' => $standing->won,
                'drawn' => $standing->drawn,
                'lost' => $standing->lost,
                'goals_for' => $standing->goals_for,
                'goals_against' => $standing->goals_against,
                'goal_difference' => $standing->goal_difference,
                'points' => $standing->points,
                'is_own_club' => $standing->is_own_club,
            ]);

        return response()->json(['data' => $standings]);
    }
}
