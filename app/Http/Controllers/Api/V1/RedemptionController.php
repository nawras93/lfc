<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\InsufficientPointsException;
use App\Exceptions\PlayerNotLinkedException;
use App\Exceptions\RedemptionItemNotAvailableException;
use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\ParentAccount;
use App\Models\RedemptionItem;
use App\Services\RedemptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RedemptionController extends Controller
{
    public function __construct(
        private readonly RedemptionService $redemptionService,
    ) {}

    public function items(Request $request): JsonResponse
    {
        $items = RedemptionItem::query()
            ->available()
            ->orderBy('name')
            ->get()
            ->map(fn (RedemptionItem $item) => [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'type' => $item->type->value,
                'points_cost' => $item->points_cost,
                'in_stock' => $item->stock === null || $item->stock > 0,
            ]);

        return response()->json(['data' => $items]);
    }

    public function redeem(Request $request): JsonResponse
    {
        $parent = $request->user();

        if (! $parent instanceof ParentAccount) {
            abort(403, 'Only parent accounts can redeem items.');
        }

        $data = $request->validate([
            'player_id' => ['required', 'integer', 'exists:candidates,id'],
            'redemption_item_id' => ['required', 'integer', 'exists:redemption_items,id'],
        ]);

        $player = Candidate::query()->findOrFail($data['player_id']);
        $item = RedemptionItem::query()->findOrFail($data['redemption_item_id']);

        try {
            $redemption = $this->redemptionService->redeem($parent, $player, $item);
        } catch (PlayerNotLinkedException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (RedemptionItemNotAvailableException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (InsufficientPointsException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $redemption->load(['item', 'player']);

        return response()->json([
            'data' => [
                'id' => $redemption->id,
                'voucher_code' => $redemption->voucher_code,
                'points_spent' => $redemption->points_spent,
                'status' => $redemption->status->value,
                'item' => [
                    'name' => $redemption->item->name,
                    'type' => $redemption->item->type->value,
                ],
                'player_name' => $redemption->player->full_name,
                'created_at' => $redemption->created_at->toIso8601String(),
            ],
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $parent = $request->user();

        if (! $parent instanceof ParentAccount) {
            abort(403, 'Only parent accounts can view redemption history.');
        }

        $redemptions = $parent->redemptions()
            ->with(['item', 'player'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'voucher_code' => $r->voucher_code,
                'points_spent' => $r->points_spent,
                'status' => $r->status->value,
                'item' => [
                    'name' => $r->item->name,
                    'type' => $r->item->type->value,
                ],
                'player_name' => $r->player->full_name,
                'fulfilled_at' => $r->fulfilled_at?->toIso8601String(),
                'created_at' => $r->created_at->toIso8601String(),
            ]);

        return response()->json(['data' => $redemptions]);
    }
}
