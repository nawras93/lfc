<?php

namespace Tests\Feature;

use App\Enums\OfferAudience;
use App\Models\Offer;
use App\Models\ParentAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferTest extends TestCase
{
    use RefreshDatabase;

    private ParentAccount $parent;
    private ParentAccount $vvipParent;
    private Offer $allOffer;
    private Offer $vvipOffer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->parent = ParentAccount::factory()->create();
        $this->vvipParent = ParentAccount::factory()->create(['is_vvip' => true]);

        $this->allOffer = Offer::query()->create([
            'title' => 'All Parents Offer',
            'body' => 'Everyone can see this.',
            'audience' => OfferAudience::All,
            'is_published' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDays(30),
        ]);

        $this->vvipOffer = Offer::query()->create([
            'title' => 'VVIP Exclusive Offer',
            'body' => 'Only VVIP parents.',
            'audience' => OfferAudience::VVIP,
            'is_published' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDays(30),
        ]);
    }

    private function parentToken(ParentAccount $p): string
    {
        return $p->createToken('mobile')->plainTextToken;
    }

    public function test_non_vvip_parent_sees_only_all_offers(): void
    {
        $response = $this->withToken($this->parentToken($this->parent))
            ->getJson('/api/v1/offers');

        $response->assertOk();
        $titles = collect($response->json('data'))->pluck('title');

        $this->assertContains('All Parents Offer', $titles);
        $this->assertNotContains('VVIP Exclusive Offer', $titles);
    }

    public function test_vvip_parent_sees_all_and_vvip_offers(): void
    {
        $response = $this->withToken($this->parentToken($this->vvipParent))
            ->getJson('/api/v1/offers');

        $response->assertOk();
        $titles = collect($response->json('data'))->pluck('title');

        $this->assertContains('All Parents Offer', $titles);
        $this->assertContains('VVIP Exclusive Offer', $titles);
    }

    public function test_unpublished_offer_not_visible_to_anyone(): void
    {
        $unpublished = Offer::query()->create([
            'title' => 'Unpublished',
            'body' => 'Hidden.',
            'audience' => OfferAudience::All,
            'is_published' => false,
        ]);

        $response = $this->withToken($this->parentToken($this->parent))
            ->getJson('/api/v1/offers');

        $titles = collect($response->json('data'))->pluck('title');
        $this->assertNotContains('Unpublished', $titles);

        $vvipResponse = $this->withToken($this->parentToken($this->vvipParent))
            ->getJson('/api/v1/offers');

        $vvipTitles = collect($vvipResponse->json('data'))->pluck('title');
        $this->assertNotContains('Unpublished', $vvipTitles);
    }

    public function test_expired_offer_not_visible(): void
    {
        Offer::query()->create([
            'title' => 'Expired Offer',
            'body' => 'Gone.',
            'audience' => OfferAudience::All,
            'is_published' => true,
            'valid_from' => now()->subDays(60),
            'valid_until' => now()->subDays(30),
        ]);

        $response = $this->withToken($this->parentToken($this->parent))
            ->getJson('/api/v1/offers');

        $titles = collect($response->json('data'))->pluck('title');
        $this->assertNotContains('Expired Offer', $titles);
    }

    public function test_future_offer_not_visible(): void
    {
        Offer::query()->create([
            'title' => 'Future Offer',
            'body' => 'Not yet.',
            'audience' => OfferAudience::All,
            'is_published' => true,
            'valid_from' => now()->addDays(10),
            'valid_until' => now()->addDays(30),
        ]);

        $response = $this->withToken($this->parentToken($this->parent))
            ->getJson('/api/v1/offers');

        $titles = collect($response->json('data'))->pluck('title');
        $this->assertNotContains('Future Offer', $titles);
    }

    public function test_visible_to_scope_works_directly(): void
    {
        $nonVvipOffers = Offer::query()->visibleTo($this->parent)->get();
        $nonVvipTitles = $nonVvipOffers->pluck('title');
        $this->assertContains('All Parents Offer', $nonVvipTitles);
        $this->assertNotContains('VVIP Exclusive Offer', $nonVvipTitles);

        $vvipOffers = Offer::query()->visibleTo($this->vvipParent)->get();
        $vvipTitles = $vvipOffers->pluck('title');
        $this->assertContains('All Parents Offer', $vvipTitles);
        $this->assertContains('VVIP Exclusive Offer', $vvipTitles);
    }

    public function test_api_offers_requires_authentication(): void
    {
        $this->getJson('/api/v1/offers')->assertUnauthorized();
    }

    public function test_offer_audience_persists_and_defaults(): void
    {
        $response = $this->withToken($this->parentToken($this->parent))
            ->getJson('/api/v1/offers');

        $response->assertOk();
        $titles = collect($response->json('data'))->pluck('title');
        $this->assertContains('All Parents Offer', $titles);
        $this->assertNotContains('VVIP Exclusive Offer', $titles);
        // Ensure no VVIP-only offers leak through
        foreach ($response->json('data') as $offer) {
            $this->assertSame('all', $offer['audience']);
        }
    }

    public function test_vvip_parent_model_flag(): void
    {
        $this->assertFalse($this->parent->fresh()->is_vvip);
        $this->assertTrue($this->vvipParent->fresh()->is_vvip);
    }
}
