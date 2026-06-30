<?php

namespace Database\Factories;

use App\Models\ParentAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParentAccount>
 */
class ParentAccountFactory extends Factory
{
    protected $model = ParentAccount::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'phone' => fake()->phoneNumber(),
            'whatsapp' => fake()->phoneNumber(),
            'invitation_token' => null,
            'invited_at' => null,
            'accepted_at' => now(),
        ];
    }

    public function invited(): static
    {
        return $this->state(fn (): array => [
            'password' => null,
            'invitation_token' => fake()->sha256(),
            'invited_at' => now(),
            'accepted_at' => null,
        ]);
    }
}
