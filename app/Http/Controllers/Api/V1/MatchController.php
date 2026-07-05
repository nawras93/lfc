<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Fixture;
use Illuminate\Http\JsonResponse;

class MatchController extends Controller
{
    public function fixtures(): JsonResponse
    {
        $fixtures = Fixture::query()
            ->whereNull('our_score')
            ->whereNull('opponent_score')
            ->where('kickoff_at', '>', now())
            ->orderBy('kickoff_at')
            ->get()
            ->map(fn (Fixture $fixture) => $this->serialize($fixture));

        return response()->json(['data' => $fixtures]);
    }

    public function results(): JsonResponse
    {
        $results = Fixture::query()
            ->whereNotNull('our_score')
            ->whereNotNull('opponent_score')
            ->orderByDesc('kickoff_at')
            ->get()
            ->map(fn (Fixture $fixture) => $this->serialize($fixture));

        return response()->json(['data' => $results]);
    }

    private function serialize(Fixture $fixture): array
    {
        return [
            'id' => $fixture->id,
            'opponent' => $fixture->opponent,
            'competition' => $fixture->competition,
            'is_home' => $fixture->is_home,
            'venue' => $fixture->venue,
            'kickoff_at' => $fixture->kickoff_at?->toIso8601String(),
            'status' => $fixture->status?->value,
            'our_score' => $fixture->our_score,
            'opponent_score' => $fixture->opponent_score,
        ];
    }
}
