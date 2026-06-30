<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\PointTransactionType;
use App\Models\ParentAccount;
use App\Models\PointTransaction;
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
                'points' => 500,
                'created_by' => $admin?->id,
            ],
        );
    }
}
