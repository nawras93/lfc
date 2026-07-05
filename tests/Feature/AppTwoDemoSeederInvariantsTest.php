<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\AppKey;
use App\Enums\LedgerUnit;
use App\Enums\OfferAudience;
use App\Models\AttendanceScan;
use App\Models\Fixture;
use App\Models\MembershipTier;
use App\Models\NewsPost;
use App\Models\Offer;
use App\Models\ParentAccount;
use App\Models\PointTransaction;
use App\Models\Standing;
use App\Support\AppContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppTwoDemoSeederInvariantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_app_two_demo_seed_supports_the_supporter_app_walkthrough(): void
    {
        $this->seed();

        $member = ParentAccount::withoutAppScope()
            ->where('email', 'member.demo@lfc.test')
            ->firstOrFail();
        $vvip = ParentAccount::withoutAppScope()
            ->where('email', 'vvip.member.demo@lfc.test')
            ->firstOrFail();
        $platinum = MembershipTier::withoutAppScope()
            ->where('app', AppKey::AppTwo)
            ->where('name', 'Platinum')
            ->firstOrFail();

        $this->assertSame(AppKey::AppTwo, $member->app);
        $this->assertSame(AccountType::Member, $member->account_type);
        $this->assertSame(2.5, $member->discountPercent());
        $this->assertLessThanOrEqual(10.0, $member->discountPercent());

        $memberDiscountTransactions = $member->pointTransactions()
            ->where('unit', LedgerUnit::DiscountPct->value)
            ->latest()
            ->get();

        $this->assertCount(5, $memberDiscountTransactions);
        $this->assertTrue($memberDiscountTransactions->every(
            fn (PointTransaction $transaction): bool => $transaction->points === 50
                && $transaction->source_type === (new AttendanceScan)->getMorphClass(),
        ));

        $this->assertSame(AppKey::AppTwo, $vvip->app);
        $this->assertSame(AccountType::VvipMember, $vvip->account_type);
        $this->assertSame($platinum->id, $vvip->membership_tier_id);
        $this->assertSame('LSC-000123', $vvip->member_number);
        $this->assertSame('2027-06-30', $vvip->membership_valid_until?->toDateString());
        $this->assertGreaterThanOrEqual(3, $platinum->benefits()->count());

        $appTwoNews = NewsPost::withoutAppScope()->forApp(AppKey::AppTwo)->published()->get();
        $appTwoFixtures = Fixture::withoutAppScope()->forApp(AppKey::AppTwo)->get();
        $appTwoStandings = Standing::withoutAppScope()->forApp(AppKey::AppTwo)->get();
        $appTwoOffers = Offer::withoutAppScope()->forApp(AppKey::AppTwo)->get();

        $this->assertGreaterThanOrEqual(3, $appTwoNews->count());
        $this->assertGreaterThanOrEqual(1, $appTwoFixtures->filter->isPlayed()->count());
        $this->assertGreaterThanOrEqual(1, $appTwoFixtures->filter->isUpcoming()->count());
        $this->assertGreaterThanOrEqual(1, $appTwoFixtures->filter->isOpenForScanning()->count());
        $this->assertSame(1, $appTwoStandings->where('is_own_club', true)->count());
        $this->assertTrue($appTwoOffers->contains(fn (Offer $offer): bool => $offer->audience === OfferAudience::All));
        $this->assertTrue($appTwoOffers->contains(fn (Offer $offer): bool => $offer->audience === OfferAudience::VVIP));

        $memberOfferTitles = Offer::query()
            ->forApp($member->app)
            ->visibleTo($member)
            ->pluck('title')
            ->all();
        $vvipOfferTitles = Offer::query()
            ->forApp($vvip->app)
            ->visibleTo($vvip)
            ->pluck('title')
            ->all();

        $this->assertContains('Supporter scarf bundle for opening night', $memberOfferTitles);
        $this->assertContains('Members training ground tour draw', $memberOfferTitles);
        $this->assertNotContains('Platinum hospitality suite invitation', $memberOfferTitles);
        $this->assertNotContains('Early Bird Registration Discount', $memberOfferTitles);
        $this->assertNotContains('VVIP Lounge Access — Al Thumama Match', $memberOfferTitles);

        $this->assertContains('Supporter scarf bundle for opening night', $vvipOfferTitles);
        $this->assertContains('Members training ground tour draw', $vvipOfferTitles);
        $this->assertContains('Platinum hospitality suite invitation', $vvipOfferTitles);
        $this->assertNotContains('Early Bird Registration Discount', $vvipOfferTitles);
        $this->assertNotContains('VVIP Lounge Access — Al Thumama Match', $vvipOfferTitles);

        $context = app(AppContext::class);

        try {
            $context->setCurrent(AppKey::AppOne);

            $this->assertFalse(
                ParentAccount::query()->where('email', 'member.demo@lfc.test')->exists(),
            );
            $this->assertFalse(
                NewsPost::query()->where('title', 'Lusail SC launches supporter membership for the new season')->exists(),
            );
        } finally {
            $context->clear();
        }
    }
}
