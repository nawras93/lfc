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
                'name_ar' => 'إعفاء من رسوم التسجيل',
                'description' => 'Waive the next season registration fee for one player.',
                'description_ar' => 'إعفاء من رسوم تسجيل الموسم القادم للاعب واحد.',
                'type' => RedemptionType::Fee,
                'points_cost' => 200,
                'stock' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Training Kit Bundle',
                'name_ar' => 'طقم تدريب متكامل',
                'description' => 'Official LFC training jersey, shorts, and socks.',
                'description_ar' => 'قميص وشورت وجوارب تدريب رسمية من نادي لوسيل.',
                'type' => RedemptionType::Merch,
                'points_cost' => 80,
                'stock' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Match Day VIP Pass',
                'name_ar' => 'تذكرة كبار الشخصيات ليوم المباراة',
                'description' => 'VIP access for parent + player to a home match.',
                'description_ar' => 'دخول VIP لولي الأمر واللاعب لإحدى المباريات على أرضنا.',
                'type' => RedemptionType::Event,
                'points_cost' => 150,
                'stock' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'LFC Backpack',
                'name_ar' => 'حقيبة ظهر لوسيل',
                'description' => 'Limited edition LFC branded backpack.',
                'description_ar' => 'حقيبة ظهر بشعار نادي لوسيل، إصدار محدود.',
                'type' => RedemptionType::Merch,
                'points_cost' => 50,
                'stock' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Water Bottle',
                'name_ar' => 'زجاجة ماء',
                'description' => 'LFC branded aluminium water bottle.',
                'description_ar' => 'زجاجة ماء ألمنيوم بشعار نادي لوسيل.',
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
