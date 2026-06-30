<?php

namespace Tests\Feature;

use App\Enums\AccountType;
use App\Enums\PointTransactionType;
use App\Enums\RedemptionStatus;
use App\Enums\RedemptionType;
use App\Models\ParentAccount;
use App\Models\PointTransaction;
use App\Models\RedemptionItem;
use App\Models\User;
use App\Services\PointsEngine;
use App\Services\RedemptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VvipAccountTest extends TestCase
{
    use RefreshDatabase;

    private ParentAccount $vvipAccount;
    private ParentAccount $parent;
    private PointsEngine $engine;
    private RedemptionService $redemptionService;
    private RedemptionItem $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->engine = app(PointsEngine::class);
        $this->redemptionService = app(RedemptionService::class);

        $this->vvipAccount = ParentAccount::factory()->create([
            'is_vvip' => true,
            'account_type' => AccountType::VvipClient,
        ]);

        $this->parent = ParentAccount::factory()->create([
            'is_vvip' => false,
            'account_type' => AccountType::Parent,
        ]);

        $this->item = RedemptionItem::query()->create([
            'name' => 'Account Test Item',
            'type' => RedemptionType::Merch,
            'points_cost' => 50,
            'stock' => 10,
            'is_active' => true,
        ]);
    }

    private function vvipToken(): string
    {
        return $this->vvipAccount->createToken('mobile')->plainTextToken;
    }

    private function parentToken(): string
    {
        return $this->parent->createToken('mobile')->plainTextToken;
    }

    /** Ledger: exactly-one-owner — both set throws */
    public function test_point_transaction_both_owners_set_throws(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('cannot belong to both');

        PointTransaction::query()->create([
            'candidate_id' => 1,
            'parent_account_id' => $this->vvipAccount->id,
            'type' => PointTransactionType::Adjust,
            'points' => 10,
        ]);
    }

    /** Ledger: exactly-one-owner — neither set throws */
    public function test_point_transaction_no_owner_throws(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('must belong to either');

        PointTransaction::query()->create([
            'type' => PointTransactionType::Adjust,
            'points' => 10,
        ]);
    }

    /** Ledger: existing player-only transactions still work */
    public function test_existing_player_transaction_path_unchanged(): void
    {
        $player = \App\Models\Candidate::factory()->create([
            'season_id' => \App\Models\Season::query()->firstOrFail()->id,
        ]);

        $txn = PointTransaction::query()->create([
            'candidate_id' => $player->id,
            'type' => PointTransactionType::Earn,
            'points' => 50,
        ]);

        $this->assertSame(50, $player->fresh()->pointsBalance());
        $this->assertNull($txn->parent_account_id);
    }

    /** Account balance = Σ account transactions */
    public function test_account_balance_is_sum_of_transactions(): void
    {
        PointTransaction::query()->create([
            'parent_account_id' => $this->vvipAccount->id,
            'type' => PointTransactionType::Adjust,
            'points' => 100,
            'reason' => 'test',
        ]);
        PointTransaction::query()->create([
            'parent_account_id' => $this->vvipAccount->id,
            'type' => PointTransactionType::Redeem,
            'points' => -30,
        ]);

        $this->assertSame(70, $this->vvipAccount->fresh()->pointsBalance());
    }

    /** grantToAccount writes audited adjust and moves account balance */
    public function test_grant_to_account_writes_audited_adjust(): void
    {
        $admin = User::query()->firstOrFail();

        $txn = $this->engine->grantToAccount($this->vvipAccount, 200, 'Welcome bonus', $admin);

        $this->assertSame($this->vvipAccount->id, $txn->parent_account_id);
        $this->assertSame(PointTransactionType::Adjust, $txn->type);
        $this->assertSame(200, $txn->points);
        $this->assertSame('Welcome bonus', $txn->reason);
        $this->assertSame($admin->id, $txn->created_by);
        $this->assertSame(200, $this->vvipAccount->fresh()->pointsBalance());
    }

    /** VVIP client redeems from account balance */
    public function test_vvip_client_redeems_from_account_balance(): void
    {
        $admin = User::query()->firstOrFail();
        $this->engine->grantToAccount($this->vvipAccount, 200, 'Starting balance', $admin);

        $redemption = $this->redemptionService->redeemForAccount($this->vvipAccount, $this->item);

        $this->assertSame(50, $redemption->points_spent);
        $this->assertSame(RedemptionStatus::Issued, $redemption->status);
        $this->assertNotNull($redemption->voucher_code);
        $this->assertSame($this->vvipAccount->id, $redemption->parent_account_id);
        $this->assertNull($redemption->candidate_id);

        $this->assertSame(150, $this->vvipAccount->fresh()->pointsBalance());

        $txn = $redemption->transactions()->first();
        $this->assertNotNull($txn);
        $this->assertSame(PointTransactionType::Redeem, $txn->type);
        $this->assertSame(-50, $txn->points);
        $this->assertSame($this->vvipAccount->id, $txn->parent_account_id);
        $this->assertNull($txn->candidate_id);
    }

    /** Insufficient account balance → rejected, no stock change */
    public function test_insufficient_account_balance_rejected(): void
    {
        $expensiveItem = RedemptionItem::query()->create([
            'name' => 'Very Expensive',
            'type' => RedemptionType::Merch,
            'points_cost' => 99999,
            'stock' => 10,
            'is_active' => true,
        ]);

        $stockBefore = $expensiveItem->fresh()->stock;

        $this->expectException(\App\Exceptions\InsufficientPointsException::class);
        $this->redemptionService->redeemForAccount($this->vvipAccount, $expensiveItem);

        $this->assertDatabaseCount('redemptions', 0);
        $this->assertSame($stockBefore, $expensiveItem->fresh()->stock);
    }

    /** Account redemption stock decrements never oversells */
    public function test_account_redemption_stock_decrements(): void
    {
        $admin = User::query()->firstOrFail();
        $this->engine->grantToAccount($this->vvipAccount, 500, 'Starting', $admin);

        $limitedItem = RedemptionItem::query()->create([
            'name' => 'Limited',
            'type' => RedemptionType::Merch,
            'points_cost' => 10,
            'stock' => 2,
            'is_active' => true,
        ]);

        $this->redemptionService->redeemForAccount($this->vvipAccount, $limitedItem);
        $this->assertSame(1, $limitedItem->fresh()->stock);

        $this->redemptionService->redeemForAccount($this->vvipAccount, $limitedItem);
        $this->assertSame(0, $limitedItem->fresh()->stock);

        $this->expectException(\App\Exceptions\RedemptionItemNotAvailableException::class);
        $this->redemptionService->redeemForAccount($this->vvipAccount, $limitedItem);
    }

    /** API: /me shows account_type for vvip_client */
    public function test_me_shows_account_type(): void
    {
        $response = $this->withToken($this->vvipToken())
            ->getJson('/api/v1/me');

        $response->assertOk();
        $response->assertJsonPath('data.account_type', AccountType::VvipClient->value);
        $response->assertJsonPath('data.is_vvip', true);
    }

    /** API: /me shows account_balance */
    public function test_me_shows_account_balance(): void
    {
        $admin = User::query()->firstOrFail();
        $this->engine->grantToAccount($this->vvipAccount, 500, 'Starting', $admin);

        $response = $this->withToken($this->vvipToken())
            ->getJson('/api/v1/me');

        $response->assertOk();
        $response->assertJsonPath('data.account_balance', 500);
    }

    /** API: POST /redemptions (no player_id) as vvip_client → success */
    public function test_api_vvip_client_redeem_no_player_id_success(): void
    {
        $admin = User::query()->firstOrFail();
        $this->engine->grantToAccount($this->vvipAccount, 200, 'Starting', $admin);

        $response = $this->withToken($this->vvipToken())
            ->postJson('/api/v1/redemptions', [
                'redemption_item_id' => $this->item->id,
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['data' => [
            'id', 'voucher_code', 'points_spent', 'status', 'item', 'player_name', 'created_at',
        ]]);
        $response->assertJsonPath('data.player_name', null);
    }

    /** API: POST /redemptions (no player_id) as plain parent → 422 */
    public function test_api_parent_no_player_id_returns_422(): void
    {
        $response = $this->withToken($this->parentToken())
            ->postJson('/api/v1/redemptions', [
                'redemption_item_id' => $this->item->id,
            ]);

        $response->assertStatus(422);
    }

    /** API: POST /redemptions insufficient account balance → 422 */
    public function test_api_vvip_client_insufficient_balance_returns_422(): void
    {
        $expensiveItem = RedemptionItem::query()->create([
            'name' => 'Expensive',
            'type' => RedemptionType::Merch,
            'points_cost' => 99999,
            'stock' => null,
            'is_active' => true,
        ]);

        $response = $this->withToken($this->vvipToken())
            ->postJson('/api/v1/redemptions', [
                'redemption_item_id' => $expensiveItem->id,
            ]);

        $response->assertStatus(422);
    }

    /** API: GET /offers returns VVIP offers for vvip_client */
    public function test_api_vvip_client_sees_vvip_offers(): void
    {
        $allOffer = \App\Models\Offer::query()->create([
            'title' => 'All Offer',
            'body' => 'test',
            'audience' => \App\Enums\OfferAudience::All,
            'is_published' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDays(30),
        ]);

        $vvipOffer = \App\Models\Offer::query()->create([
            'title' => 'VVIP Exclusive',
            'body' => 'test',
            'audience' => \App\Enums\OfferAudience::VVIP,
            'is_published' => true,
            'valid_from' => now()->subDay(),
            'valid_until' => now()->addDays(30),
        ]);

        $response = $this->withToken($this->vvipToken())
            ->getJson('/api/v1/offers');

        $response->assertOk();
        $titles = collect($response->json('data'))->pluck('title');
        $this->assertContains('All Offer', $titles);
        $this->assertContains('VVIP Exclusive', $titles);
    }

    /** API: GET /redemptions history includes account redemptions with null player */
    public function test_api_vvip_client_history_shows_account_redemptions(): void
    {
        $admin = User::query()->firstOrFail();
        $this->engine->grantToAccount($this->vvipAccount, 200, 'Starting', $admin);
        $this->redemptionService->redeemForAccount($this->vvipAccount, $this->item);

        $response = $this->withToken($this->vvipToken())
            ->getJson('/api/v1/redemptions');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertNull($response->json('data.0.player_name'));
    }
}
