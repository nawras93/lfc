<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\AccountType;
use App\Enums\AppKey;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ParentAccountResource;
use App\Models\ParentAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:parent_accounts,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string'],
        ]);

        $parent = ParentAccount::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'app' => AppKey::AppTwo,
            'account_type' => AccountType::Member,
            'is_vvip' => false,
            'accepted_at' => now(),
        ]);

        return response()->json([
            'token' => $parent->createToken('mobile')->plainTextToken,
            'parent' => ParentAccountResource::make($parent),
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $parent = ParentAccount::query()
            ->whereRaw('lower(email) = ?', [Str::lower($credentials['email'])])
            ->first();

        if (! $parent || ! $parent->password || ! Hash::check($credentials['password'], $parent->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return response()->json([
            'token' => $parent->createToken('mobile')->plainTextToken,
            'parent' => ParentAccountResource::make($parent),
        ]);
    }

    public function acceptInvite(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $parent = ParentAccount::query()
            ->where('invitation_token', $data['token'])
            ->first();

        if (! $parent) {
            throw ValidationException::withMessages([
                'token' => ['The invitation token is invalid.'],
            ]);
        }

        $parent->forceFill([
            'password' => $data['password'],
            'accepted_at' => now(),
            'invitation_token' => null,
        ])->save();

        return response()->json([
            'token' => $parent->createToken('mobile')->plainTextToken,
            'parent' => ParentAccountResource::make($parent),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }
}
