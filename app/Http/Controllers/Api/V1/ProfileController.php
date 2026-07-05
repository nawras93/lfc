<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ParentAccountResource;
use App\Models\ParentAccount;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __invoke(Request $request): ParentAccountResource
    {
        $parent = $request->user();

        if (! $parent instanceof ParentAccount) {
            abort(403, 'Only parent accounts can view this profile.');
        }

        return ParentAccountResource::make($parent->loadMissing('membershipTier'));
    }
}
