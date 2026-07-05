<?php

namespace Database\Factories;

use App\Models\MembershipBenefit;
use App\Models\MembershipTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MembershipBenefit>
 */
class MembershipBenefitFactory extends Factory
{
    protected $model = MembershipBenefit::class;

    public function definition(): array
    {
        return [
            'membership_tier_id' => MembershipTier::factory(),
            'title' => fake()->sentence(3),
            'title_ar' => 'ميزة حصرية',
            'description' => fake()->sentence(),
            'description_ar' => 'وصف الميزة',
            'icon' => 'heroicon-o-star',
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
