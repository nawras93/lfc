<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AttendanceScan;
use App\Models\Fixture;
use App\Models\ParentAccount;
use App\Services\ScanTokenService;
use App\Services\PointsEngine;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScanController extends Controller
{
    public function __construct(
        private readonly ScanTokenService $scanTokenService,
        private readonly PointsEngine $pointsEngine,
    ) {}

    public function token(Request $request): JsonResponse
    {
        $parent = $request->user();

        if (! $parent instanceof ParentAccount) {
            abort(403, 'Only parent accounts can request a scan token.');
        }

        $result = $this->scanTokenService->issue($parent);

        return response()->json([
            'token' => $result['token'],
            'expires_at' => $result['expires_at'],
        ]);
    }

    public function fixtures(Request $request): JsonResponse
    {
        $staff = $request->user();

        if ($staff instanceof ParentAccount) {
            abort(403, 'Staff endpoint — parent tokens are not accepted.');
        }

        if (! $staff->hasAnyRole(['Admin', 'Coach', 'Management'])) {
            abort(403, 'You do not have scanner privileges.');
        }

        $fixtures = Fixture::query()
            ->with('team')
            ->where('status', \App\Enums\FixtureStatus::OpenForScanning)
            ->orderBy('kickoff_at')
            ->get()
            ->filter(fn (Fixture $fixture) => $fixture->isOpenForScanning())
            ->values()
            ->map(fn (Fixture $fixture) => [
                'id' => $fixture->id,
                'team_name' => $fixture->team?->name,
                'opponent' => $fixture->opponent,
                'venue' => $fixture->venue,
                'kickoff_at' => $fixture->kickoff_at?->toIso8601String(),
                'scan_closes_at' => $fixture->scan_closes_at?->toIso8601String(),
            ]);

        return response()->json(['data' => $fixtures]);
    }

    public function scan(Request $request): JsonResponse
    {
        $staff = $request->user();

        if ($staff instanceof ParentAccount) {
            abort(403, 'Staff endpoint — parent tokens are not accepted.');
        }

        if (! $staff->hasAnyRole(['Admin', 'Coach', 'Management'])) {
            abort(403, 'You do not have scanner privileges.');
        }

        $data = $request->validate([
            'fixture_id' => ['required', 'integer', 'exists:fixtures,id'],
            'token' => ['required', 'string'],
        ]);

        $parentId = $this->scanTokenService->verify($data['token']);

        if ($parentId === null) {
            return response()->json(['message' => 'Invalid or expired QR.'], 422);
        }

        $fixture = Fixture::query()->findOrFail($data['fixture_id']);

        if (! $fixture->isOpenForScanning()) {
            return response()->json(['message' => 'Match is not open for scanning.'], 422);
        }

        $parent = ParentAccount::query()->findOrFail($parentId);

        $qualifyingPlayers = $parent->players()
            ->where('candidates.team_id', $fixture->team_id)
            ->where('candidates.is_player', true)
            ->get();

        if ($qualifyingPlayers->isEmpty()) {
            return response()->json(['message' => 'No linked player on this match\'s team.'], 422);
        }

        try {
            $result = DB::transaction(function () use ($parent, $fixture, $staff, $qualifyingPlayers) {
                $scan = AttendanceScan::query()->create([
                    'parent_account_id' => $parent->id,
                    'fixture_id' => $fixture->id,
                    'scanned_by' => $staff->id,
                    'scanned_at' => Carbon::now(),
                ]);

                $credited = [];

                foreach ($qualifyingPlayers as $player) {
                    $txn = $this->pointsEngine->credit($player, $fixture, $scan);

                    $credited[] = [
                        'player_id' => $player->id,
                        'player_name' => $player->full_name,
                        'points' => $txn?->points ?? 0,
                    ];
                }

                return ['scan' => $scan, 'credited' => $credited];
            });
        } catch (UniqueConstraintViolationException $e) {
            return response()->json(['message' => 'Already scanned for this match.'], 409);
        }

        $scan = $result['scan'];
        $credited = $result['credited'];
        $total = array_sum(array_column($credited, 'points'));

        return response()->json([
            'scan_id' => $scan->id,
            'credited' => $credited,
            'total_points' => $total,
        ]);
    }
}
