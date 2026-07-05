<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MembershipBenefit;
use App\Models\ParentAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BenefitController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $parent = $request->user();

        if (! $parent instanceof ParentAccount) {
            abort(403, 'Only parent accounts can view membership benefits.');
        }

        $parent->loadMissing('membershipTier.benefits');

        if ($parent->membershipTier === null) {
            return response()->json(['data' => null]);
        }

        return response()->json([
            'data' => [
                'tier' => [
                    'name' => $parent->membershipTier->localized('name'),
                    'level' => $parent->membershipTier->level,
                    'accent_color' => $parent->membershipTier->accent_color,
                ],
                'member_number' => $parent->member_number,
                'valid_until' => $parent->membership_valid_until?->toDateString(),
                'benefits' => $parent->membershipTier->benefits->map(fn (MembershipBenefit $benefit): array => [
                    'title' => $benefit->localized('title'),
                    'description' => $benefit->localized('description'),
                    'icon' => $benefit->icon,
                ])->values()->all(),
            ],
        ]);
    }
}
