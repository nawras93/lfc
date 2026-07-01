<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AttendanceScan;
use App\Models\Candidate;
use App\Models\ParentAccount;
use App\Models\PointTransaction;
use App\Models\Redemption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PointTransactionController extends Controller
{
    public function playerHistory(Request $request, Candidate $player): JsonResponse
    {
        $parent = $request->user();

        if (! $parent instanceof ParentAccount) {
            abort(403, 'Only parent accounts can view player transactions.');
        }

        $scopedPlayer = $parent->players()
            ->whereKey($player->getKey())
            ->firstOrFail();

        $transactions = $scopedPlayer->pointTransactions()
            ->latest()
            ->get();

        return response()->json([
            'data' => $transactions->map(fn (PointTransaction $transaction) => $this->mapTransaction($transaction)),
        ]);
    }

    public function accountHistory(Request $request): JsonResponse
    {
        $parent = $request->user();

        if (! $parent instanceof ParentAccount) {
            abort(403, 'Only parent accounts can view account transactions.');
        }

        $transactions = $parent->pointTransactions()
            ->latest()
            ->get();

        return response()->json([
            'data' => $transactions->map(fn (PointTransaction $transaction) => $this->mapTransaction($transaction)),
        ]);
    }

    private function mapTransaction(PointTransaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'points' => $transaction->points,
            'type' => $transaction->type->value,
            'reason' => $transaction->reason,
            'source' => match ($transaction->source_type) {
                (new AttendanceScan)->getMorphClass() => 'scan',
                (new Redemption)->getMorphClass() => 'redemption',
                default => null,
            },
            'created_at' => $transaction->created_at->toIso8601String(),
        ];
    }
}
