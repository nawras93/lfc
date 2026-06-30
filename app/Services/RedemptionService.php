<?php

namespace App\Services;

use App\Enums\RedemptionStatus;
use App\Exceptions\InsufficientPointsException;
use App\Exceptions\RedemptionItemNotAvailableException;
use App\Exceptions\PlayerNotLinkedException;
use App\Models\Candidate;
use App\Models\ParentAccount;
use App\Models\Redemption;
use App\Models\RedemptionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RedemptionService
{
    public function __construct(
        private readonly PointsEngine $pointsEngine,
    ) {}

    public function redeem(ParentAccount $parent, Candidate $player, RedemptionItem $item): Redemption
    {
        if (! $parent->players()->where('candidate_id', $player->id)->exists()) {
            throw new PlayerNotLinkedException('Player is not linked to this parent account.');
        }

        return DB::transaction(function () use ($parent, $player, $item) {
            $item = RedemptionItem::query()->lockForUpdate()->findOrFail($item->id);

            if (! $item->is_active
                || ($item->stock !== null && $item->stock <= 0)
                || ($item->valid_from !== null && $item->valid_from->isFuture())
                || ($item->valid_until !== null && $item->valid_until->isPast())) {
                throw new RedemptionItemNotAvailableException('This item is not available for redemption.');
            }

            if ($player->pointsBalance() < $item->points_cost) {
                throw new InsufficientPointsException('Insufficient points for this redemption.');
            }

            if ($item->stock !== null) {
                $item->decrement('stock');
            }

            $voucherCode = $this->generateUniqueVoucherCode();

            $redemption = Redemption::query()->create([
                'parent_account_id' => $parent->id,
                'candidate_id' => $player->id,
                'redemption_item_id' => $item->id,
                'points_spent' => $item->points_cost,
                'voucher_code' => $voucherCode,
                'status' => RedemptionStatus::Issued,
            ]);

            $this->pointsEngine->redeem($player, $item->points_cost, $redemption);

            return $redemption;
        });
    }

    private function generateUniqueVoucherCode(): string
    {
        do {
            $code = Str::upper(Str::random(10));
        } while (Redemption::query()->where('voucher_code', $code)->exists());

        return $code;
    }
}
