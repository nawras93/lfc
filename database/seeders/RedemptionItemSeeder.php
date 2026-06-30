<?php

namespace Database\Seeders;

use App\Enums\RedemptionType;
use App\Models\RedemptionItem;
use Illuminate\Database\Seeder;

class RedemptionItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'name' => 'Registration Fee Waiver',
                'description' => 'Waive the next season registration fee for one player.',
                'type' => RedemptionType::Fee,
                'points_cost' => 200,
                'stock' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Training Kit Bundle',
                'description' => 'Official LFC training jersey, shorts, and socks.',
                'type' => RedemptionType::Merch,
                'points_cost' => 80,
                'stock' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Match Day VIP Pass',
                'description' => 'VIP access for parent + player to a home match.',
                'type' => RedemptionType::Event,
                'points_cost' => 150,
                'stock' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'LFC Backpack',
                'description' => 'Limited edition LFC branded backpack.',
                'type' => RedemptionType::Merch,
                'points_cost' => 50,
                'stock' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Water Bottle',
                'description' => 'LFC branded aluminium water bottle.',
                'type' => RedemptionType::Merch,
                'points_cost' => 20,
                'stock' => null,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            RedemptionItem::query()->updateOrCreate(
                ['name' => $item['name']],
                $item,
            );
        }
    }
}
