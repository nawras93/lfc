<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ParentAccountResource;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __invoke(Request $request): ParentAccountResource
    {
        return ParentAccountResource::make($request->user());
    }
}
