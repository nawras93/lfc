<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $guard = 'web';

        foreach (['Admin', 'Coach', 'Management'] as $roleName) {
            Role::findOrCreate($roleName, $guard);
        }

        $admin = User::query()->updateOrCreate(
            ['email' => env('LFC_ADMIN_EMAIL', 'admin@lfc.test')],
            [
                'name' => env('LFC_ADMIN_NAME', 'LFC Admin'),
                'password' => env('LFC_ADMIN_PASSWORD', 'password'),
                'email_verified_at' => now(),
            ],
        );

        $admin->syncRoles(['Admin']);

        $this->call([
            SeasonSeeder::class,
            TeamSeeder::class,
            FixtureSeeder::class,
            DocumentTypeSeeder::class,
            PointRuleSeeder::class,
            RedemptionItemSeeder::class,
            OfferSeeder::class,
            ParentAccountSeeder::class,
            VvipClientSeeder::class,
            DemoScenariosSeeder::class,
            AppTwoDemoSeeder::class,
        ]);
    }
}
