<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use App\Models\ParentAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $parent = $request->user();

        if (! $parent instanceof ParentAccount) {
            abort(403, 'Only parent accounts can view offers.');
        }

        // App scoping comes from SetAppContextFromUser + the ScopedToApp global scope.
        $offers = Offer::query()
            ->visibleTo($parent)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Offer $offer) => [
                'id' => $offer->id,
                'title' => $offer->localized('title'),
                'body' => $offer->localized('body'),
                'audience' => $offer->audience->value,
                'valid_from' => $offer->valid_from?->toIso8601String(),
                'valid_until' => $offer->valid_until?->toIso8601String(),
                'created_at' => $offer->created_at->toIso8601String(),
            ]);

        return response()->json(['data' => $offers]);
    }
}
