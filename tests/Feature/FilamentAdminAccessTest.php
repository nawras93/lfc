<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class FilamentAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_page_is_available(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_seeded_admin_can_access_the_filament_panel(): void
    {
        $this->seed();

        $admin = User::query()->where('email', env('LFC_ADMIN_EMAIL', 'admin@lfc.test'))->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_seeded_admin_credentials_can_authenticate(): void
    {
        $this->seed();

        $authenticated = Auth::attempt([
            'email' => env('LFC_ADMIN_EMAIL', 'admin@lfc.test'),
            'password' => env('LFC_ADMIN_PASSWORD', 'password'),
        ]);

        $this->assertTrue($authenticated);
        $this->assertAuthenticated();
    }

    public function test_seeded_coach_can_access_the_filament_panel(): void
    {
        $this->seed();

        $coach = User::factory()->create();
        $coach->assignRole('Coach');

        $this->actingAs($coach)
            ->get('/admin')
            ->assertOk();
    }

    public function test_seeded_management_can_access_the_filament_panel(): void
    {
        $this->seed();

        $management = User::factory()->create();
        $management->assignRole('Management');

        $this->actingAs($management)
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_login_page_renders_rtl_when_locale_is_arabic(): void
    {
        $this->get('/admin/login?lang=ar')
            ->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false);
    }
}
