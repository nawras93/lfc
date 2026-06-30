<?php

namespace Tests\Feature;

use App\Enums\FixtureStatus;
use App\Filament\Resources\Fixtures\Pages\EditFixture;
use App\Filament\Resources\Fixtures\Pages\ListFixtures;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FixtureResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_open_for_scanning_returns_true_when_status_is_open_and_within_window(): void
    {
        $this->seed();
        $team = Team::query()->firstOrFail();

        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $team->season_id,
            'opponent' => 'Test Opponent',
            'venue' => 'Test Venue',
            'kickoff_at' => now()->addDay(),
            'scan_opens_at' => now()->subHour(),
            'scan_closes_at' => now()->addHour(),
            'status' => FixtureStatus::OpenForScanning,
        ]);

        $this->assertTrue($fixture->isOpenForScanning());
    }

    public function test_is_open_for_scanning_returns_false_when_status_is_scheduled(): void
    {
        $this->seed();
        $team = Team::query()->firstOrFail();

        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $team->season_id,
            'opponent' => 'Test Opponent',
            'venue' => 'Test Venue',
            'kickoff_at' => now()->addDay(),
            'scan_opens_at' => now()->subHour(),
            'scan_closes_at' => now()->addHour(),
            'status' => FixtureStatus::Scheduled,
        ]);

        $this->assertFalse($fixture->isOpenForScanning());
    }

    public function test_is_open_for_scanning_returns_false_when_status_is_closed(): void
    {
        $this->seed();
        $team = Team::query()->firstOrFail();

        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $team->season_id,
            'opponent' => 'Test Opponent',
            'venue' => 'Test Venue',
            'kickoff_at' => now()->addDay(),
            'scan_opens_at' => now()->subHour(),
            'scan_closes_at' => now()->addHour(),
            'status' => FixtureStatus::Closed,
        ]);

        $this->assertFalse($fixture->isOpenForScanning());
    }

    public function test_is_open_for_scanning_returns_false_when_before_scan_opens_at(): void
    {
        $this->seed();
        $team = Team::query()->firstOrFail();

        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $team->season_id,
            'opponent' => 'Test Opponent',
            'venue' => 'Test Venue',
            'kickoff_at' => now()->addDay(),
            'scan_opens_at' => now()->addHour(),
            'scan_closes_at' => now()->addHours(3),
            'status' => FixtureStatus::OpenForScanning,
        ]);

        $this->assertFalse($fixture->isOpenForScanning());
    }

    public function test_is_open_for_scanning_returns_false_when_after_scan_closes_at(): void
    {
        $this->seed();
        $team = Team::query()->firstOrFail();

        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $team->season_id,
            'opponent' => 'Test Opponent',
            'venue' => 'Test Venue',
            'kickoff_at' => now()->addDay(),
            'scan_opens_at' => now()->subHours(3),
            'scan_closes_at' => now()->subHour(),
            'status' => FixtureStatus::OpenForScanning,
        ]);

        $this->assertFalse($fixture->isOpenForScanning());
    }

    public function test_is_open_for_scanning_returns_true_when_scan_opens_at_is_null(): void
    {
        $this->seed();
        $team = Team::query()->firstOrFail();

        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $team->season_id,
            'opponent' => 'Test Opponent',
            'venue' => 'Test Venue',
            'kickoff_at' => now()->addDay(),
            'scan_opens_at' => null,
            'scan_closes_at' => now()->addHour(),
            'status' => FixtureStatus::OpenForScanning,
        ]);

        $this->assertTrue($fixture->isOpenForScanning());
    }

    public function test_is_open_for_scanning_returns_true_when_scan_closes_at_is_null(): void
    {
        $this->seed();
        $team = Team::query()->firstOrFail();

        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $team->season_id,
            'opponent' => 'Test Opponent',
            'venue' => 'Test Venue',
            'kickoff_at' => now()->addDay(),
            'scan_opens_at' => now()->subHour(),
            'scan_closes_at' => null,
            'status' => FixtureStatus::OpenForScanning,
        ]);

        $this->assertTrue($fixture->isOpenForScanning());
    }

    public function test_open_for_scanning_action_changes_status(): void
    {
        $admin = $this->actingAsAdmin();
        $team = Team::query()->firstOrFail();

        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $team->season_id,
            'opponent' => 'Test Opponent',
            'venue' => 'Test Venue',
            'kickoff_at' => now()->addDay(),
            'status' => FixtureStatus::Scheduled,
        ]);

        Livewire::test(ListFixtures::class)
            ->callTableAction('openForScanning', $fixture);

        $fixture->refresh();

        $this->assertSame(FixtureStatus::OpenForScanning, $fixture->status);
    }

    public function test_close_scanning_action_changes_status(): void
    {
        $admin = $this->actingAsAdmin();
        $team = Team::query()->firstOrFail();

        $fixture = Fixture::query()->create([
            'team_id' => $team->id,
            'season_id' => $team->season_id,
            'opponent' => 'Test Opponent',
            'venue' => 'Test Venue',
            'kickoff_at' => now()->addDay(),
            'status' => FixtureStatus::OpenForScanning,
        ]);

        Livewire::test(ListFixtures::class)
            ->callTableAction('closeScanning', $fixture);

        $fixture->refresh();

        $this->assertSame(FixtureStatus::Closed, $fixture->status);
    }

    public function test_seeder_creates_open_and_scheduled_fixtures(): void
    {
        $this->seed();

        $openFixture = Fixture::query()->where('status', FixtureStatus::OpenForScanning)->first();
        $this->assertNotNull($openFixture);
        $this->assertTrue($openFixture->isOpenForScanning());

        $scheduledFixture = Fixture::query()->where('status', FixtureStatus::Scheduled)->first();
        $this->assertNotNull($scheduledFixture);
        $this->assertFalse($scheduledFixture->isOpenForScanning());
    }

    private function actingAsAdmin(): User
    {
        $this->seed();

        $admin = User::query()->where('email', env('LFC_ADMIN_EMAIL', 'admin@lfc.test'))->firstOrFail();
        $this->actingAs($admin);

        return $admin;
    }
}
