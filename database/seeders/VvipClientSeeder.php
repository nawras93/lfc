<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\PointTransactionType;
use App\Enums\RedemptionStatus;
use App\Models\ParentAccount;
use App\Models\PointTransaction;
use App\Models\Redemption;
use App\Models\RedemptionItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class VvipClientSeeder extends Seeder
{
    public function run(): void
    {
        $vvip = ParentAccount::query()->updateOrCreate(
            ['email' => env('LFC_DEMO_VVIP_EMAIL', 'vvip.demo@lfc.test')],
            [
                'name' => 'Sheikha Demo',
                'password' => env('LFC_DEMO_VVIP_PASSWORD', 'password'),
                'phone' => '555200200',
                'whatsapp' => '555200201',
                'is_vvip' => true,
                'account_type' => AccountType::VvipClient,
                'accepted_at' => now(),
                'invited_at' => now(),
                'invitation_token' => null,
            ],
        );

        $admin = User::query()->first();

        PointTransaction::query()->firstOrCreate(
            [
                'parent_account_id' => $vvip->id,
                'type' => PointTransactionType::Adjust,
                'reason' => 'Demo account — starting points',
            ],
            [
                'points' => 520,
                'created_by' => $admin?->id,
            ],
        );

        $item = RedemptionItem::query()->where('name', 'Water Bottle')->first();

        if ($item === null) {
            return;
        }

        $redemption = Redemption::query()->updateOrCreate(
            ['voucher_code' => 'DEMO-VVIP-FULFILLED'],
            [
                'parent_account_id' => $vvip->id,
                'candidate_id' => null,
                'redemption_item_id' => $item->id,
                'points_spent' => $item->points_cost,
                'status' => RedemptionStatus::Fulfilled,
                'fulfilled_at' => now()->subDay(),
                'fulfilled_by' => $admin?->id,
            ],
        );

        PointTransaction::query()->firstOrCreate(
            [
                'parent_account_id' => $vvip->id,
                'type' => PointTransactionType::Redeem,
                'source_type' => $redemption->getMorphClass(),
                'source_id' => $redemption->id,
            ],
            [
                'points' => -1 * $item->points_cost,
                'reason' => 'Voucher issued',
                'created_by' => $admin?->id,
            ],
        );
    }
}
