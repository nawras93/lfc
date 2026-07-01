<?php

namespace Tests\Feature;

use App\Enums\RedemptionStatus;
use App\Models\Candidate;
use App\Models\Fixture;
use App\Models\Offer;
use App\Models\ParentAccount;
use App\Models\Redemption;
use App\Models\RedemptionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoSeederInvariantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seed_invariants_support_the_walkthrough(): void
    {
        $this->seed();

        $parent = ParentAccount::query()->where('email', 'parent.demo@lfc.test')->firstOrFail();
        $vvip = ParentAccount::query()->where('email', 'vvip.demo@lfc.test')->firstOrFail();
        $demoPlayer = Candidate::query()->where('email', 'parent.demo@lfc.test')->firstOrFail();
        $openFixture = Fixture::query()->where('opponent', 'Al Sadd SC')->firstOrFail();

        $this->assertSame($openFixture->team_id, $demoPlayer->team_id);
        $this->assertTrue($openFixture->isOpenForScanning());

        $parentOfferTitles = Offer::query()
            ->visibleTo($parent)
            ->pluck('title')
            ->all();
        $vvipOfferTitles = Offer::query()
            ->visibleTo($vvip)
            ->pluck('title')
            ->all();

        $this->assertNotEmpty($parentOfferTitles);
        $this->assertContains('Early Bird Registration Discount', $parentOfferTitles);
        $this->assertNotContains('VVIP Lounge Access — Al Thumama Match', $parentOfferTitles);
        $this->assertContains('VVIP Lounge Access — Al Thumama Match', $vvipOfferTitles);

        $this->assertDatabaseHas('redemption_items', [
            'name' => 'Match Day VIP Pass',
            'points_cost' => 150,
            'is_active' => true,
        ]);
        $this->assertTrue(
            RedemptionItem::query()
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('stock')->orWhere('stock', '>', 0);
                })
                ->where('points_cost', '<=', 150)
                ->exists(),
        );
        $this->assertTrue(
            RedemptionItem::query()
                ->where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('stock')->orWhere('stock', '>', 0);
                })
                ->where('points_cost', '<=', 500)
                ->exists(),
        );

        $this->assertSame(150, $demoPlayer->pointsBalance());
        $this->assertSame(500, $vvip->pointsBalance());

        $this->assertTrue(
            Redemption::query()->where('status', RedemptionStatus::Issued)->exists(),
        );
        $this->assertTrue(
            Redemption::query()->where('status', RedemptionStatus::Fulfilled)->exists(),
        );
    }
}
