<?php

namespace Database\Seeders;

use App\Enums\OfferAudience;
use App\Models\Offer;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        Offer::query()->updateOrCreate(
            ['title' => 'Early Bird Registration Discount'],
            [
                'title_ar' => 'خصم التسجيل المبكر',
                'body' => 'Register for next season by the end of this month and receive 10% off the registration fee. Open to all parents.',
                'body_ar' => 'سجّل للموسم القادم قبل نهاية هذا الشهر واحصل على خصم ١٠٪ من رسوم التسجيل. متاح لجميع أولياء الأمور.',
                'audience' => OfferAudience::All,
                'is_published' => true,
                'valid_from' => now()->subDays(5),
                'valid_until' => now()->addDays(25),
            ],
        );

        Offer::query()->updateOrCreate(
            ['title' => 'VVIP Lounge Access — Al Thumama Match'],
            [
                'title_ar' => 'دخول صالة كبار الشخصيات — مباراة الثمامة',
                'body' => 'As a VVIP member, you and your player are invited to the exclusive lounge at the next home match. Complimentary refreshments and seating.',
                'body_ar' => 'بصفتك عضو كبار الشخصيات، أنت ولاعبك مدعوّان إلى الصالة الحصرية في المباراة القادمة على أرضنا. مرطبات ومقاعد مجانية.',
                'audience' => OfferAudience::VVIP,
                'is_published' => true,
                'valid_from' => now()->subDay(),
                'valid_until' => now()->addDays(60),
            ],
        );

        Offer::query()->updateOrCreate(
            ['title' => 'Parent Workshop: Nutrition for Young Athletes'],
            [
                'title_ar' => 'ورشة لأولياء الأمور: تغذية الرياضيين الناشئين',
                'body' => 'Free online workshop with a sports nutritionist. Open to all parents. Recording will be sent to registered attendees.',
                'body_ar' => 'ورشة مجانية عبر الإنترنت مع أخصائي تغذية رياضية. متاحة لجميع أولياء الأمور. سيُرسل التسجيل إلى المشاركين المسجلين.',
                'audience' => OfferAudience::All,
                'is_published' => true,
                'valid_from' => now()->subDays(2),
                'valid_until' => now()->addDays(14),
            ],
        );
    }
}
