<?php

namespace Database\Factories;

use App\Enums\AppKey;
use App\Models\MembershipTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MembershipTier>
 */
class MembershipTierFactory extends Factory
{
    protected $model = MembershipTier::class;

    public function definition(): array
    {
        return [
            'app' => AppKey::AppTwo,
            'name' => fake()->randomElement(['Gold', 'Platinum', 'Diamond']),
            'name_ar' => fake()->randomElement(['ذهبي', 'بلاتيني', 'ماسي']),
            'level' => fake()->numberBetween(1, 5),
            'accent_color' => fake()->hexColor(),
            'is_active' => true,
        ];
    }
}
