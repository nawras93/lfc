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
                'body' => 'Register for next season by the end of this month and receive 10% off the registration fee. Open to all parents.',
                'audience' => OfferAudience::All,
                'is_published' => true,
                'valid_from' => now()->subDays(5),
                'valid_until' => now()->addDays(25),
            ],
        );

        Offer::query()->updateOrCreate(
            ['title' => 'VVIP Lounge Access — Al Thumama Match'],
            [
                'body' => 'As a VVIP member, you and your player are invited to the exclusive lounge at the next home match. Complimentary refreshments and seating.',
                'audience' => OfferAudience::VVIP,
                'is_published' => true,
                'valid_from' => now()->subDay(),
                'valid_until' => now()->addDays(60),
            ],
        );

        Offer::query()->updateOrCreate(
            ['title' => 'Parent Workshop: Nutrition for Young Athletes'],
            [
                'body' => 'Free online workshop with a sports nutritionist. Open to all parents. Recording will be sent to registered attendees.',
                'audience' => OfferAudience::All,
                'is_published' => true,
                'valid_from' => now()->subDays(2),
                'valid_until' => now()->addDays(14),
            ],
        );
    }
}
