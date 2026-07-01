<?php

namespace Tests\Feature;

use App\Enums\RedemptionStatus;
use App\Enums\RedemptionType;
use App\Models\Candidate;
use App\Models\ParentAccount;
use App\Models\Redemption;
use App\Models\RedemptionItem;
use App\Models\User;
use App\Services\RedemptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedemptionTest extends TestCase
{
    use RefreshDatabase;

    private ParentAccount $parent;
    private Candidate $player;
    private Candidate $otherPlayer;
    private RedemptionItem $item;
    private RedemptionService $redemptionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->parent = ParentAccount::factory()->create();
        $this->player = Candidate::factory()->create([
            'season_id' => \App\Models\Season::query()->firstOrFail()->id,
            'team_id' => \App\Models\Team::query()->firstOrFail()->id,
            'is_player' => true,
        ]);
        $this->otherPlayer = Candidate::factory()->create([
            'season_id' => \App\Models\Season::query()->firstOrFail()->id,
            'team_id' => \App\Models\Team::query()->firstOrFail()->id,
            'is_player' => true,
        ]);
        $this->parent->players()->attach($this->player);

        $this->item = RedemptionItem::query()->create([
            'name' => 'Test T-Shirt',
            'type' => RedemptionType::Merch,
            'points_cost' => 50,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->redemptionService = app(RedemptionService::class);

        // Give the player some points to spend
        \App\Models\PointTransaction::query()->create([
            'candidate_id' => $this->player->id,
            'type' => \App\Enums\PointTransactionType::Earn,
            'points' => 200,
        ]);
    }

    private function parentToken(): string
    {
        return $this->parent->createToken('mobile')->plainTextToken;
    }

    public function test_redeem_deducts_points_and_creates_transaction(): void
    {
        $balanceBefore = $this->player->fresh()->pointsBalance();

        $redemption = $this->redemptionService->redeem($this->parent, $this->player, $this->item);

        $this->assertSame(50, $redemption->points_spent);
        $this->assertSame(RedemptionStatus::Issued, $redemption->status);
        $this->assertNotNull($redemption->voucher_code);
        $this->assertGreaterThan(0, strlen($redemption->voucher_code));

        $balanceAfter = $this->player->fresh()->pointsBalance();
        $this->assertSame($balanceBefore - 50, $balanceAfter);

        $txn = $redemption->transactions()->first();
        $this->assertNotNull($txn);
        $this->assertSame(\App\Enums\PointTransactionType::Redeem, $txn->type);
        $this->assertSame(-50, $txn->points);
        $this->assertSame($this->player->id, $txn->candidate_id);
        $this->assertSame($redemption->getMorphClass(), $txn->source_type);
        $this->assertSame($redemption->id, $txn->source_id);
    }

    public function test_fulfill_marks_issued_voucher_and_stamps_staff(): void
    {
        $redemption = $this->redemptionService->redeem($this->parent, $this->player, $this->item);
        $staff = User::factory()->create();

        $fulfilled = $this->redemptionService->fulfill($redemption, $staff);

        $this->assertSame(RedemptionStatus::Fulfilled, $fulfilled->status);
        $this->assertNotNull($fulfilled->fulfilled_at);
        $this->assertSame($staff->id, $fulfilled->fulfilled_by);

        $fresh = $redemption->fresh();
        $this->assertSame(RedemptionStatus::Fulfilled, $fresh->status);
        $this->assertSame($staff->id, $fresh->fulfilled_by);
    }

    public function test_fulfill_rejects_already_fulfilled_voucher(): void
    {
        $redemption = $this->redemptionService->redeem($this->parent, $this->player, $this->item);
        $staff = User::factory()->create();
        $this->redemptionService->fulfill($redemption, $staff);

        $this->expectException(\App\Exceptions\RedemptionNotFulfillableException::class);
        $this->redemptionService->fulfill($redemption->fresh(), $staff);
    }

    public function test_insufficient_balance_rejected_no_redemption_no_stock_change(): void
    {
        $expensiveItem = RedemptionItem::query()->create([
            'name' => 'Expensive Item',
            'type' => RedemptionType::Merch,
            'points_cost' => 99999,
            'stock' => 10,
            'is_active' => true,
        ]);

        $stockBefore = $expensiveItem->fresh()->stock;

        $this->expectException(\App\Exceptions\InsufficientPointsException::class);
        $this->redemptionService->redeem($this->parent, $this->player, $expensiveItem);

        $this->assertDatabaseCount('redemptions', 0);
        $this->assertSame($stockBefore, $expensiveItem->fresh()->stock);
    }

    public function test_out_of_stock_item_rejected(): void
    {
        $outOfStock = RedemptionItem::query()->create([
            'name' => 'Out of Stock',
            'type' => RedemptionType::Merch,
            'points_cost' => 10,
            'stock' => 0,
            'is_active' => true,
        ]);

        $this->expectException(\App\Exceptions\RedemptionItemNotAvailableException::class);
        $this->redemptionService->redeem($this->parent, $this->player, $outOfStock);
    }

    public function test_inactive_item_rejected(): void
    {
        $inactive = RedemptionItem::query()->create([
            'name' => 'Inactive',
            'type' => RedemptionType::Merch,
            'points_cost' => 10,
            'stock' => null,
            'is_active' => false,
        ]);

        $this->expectException(\App\Exceptions\RedemptionItemNotAvailableException::class);
        $this->redemptionService->redeem($this->parent, $this->player, $inactive);
    }

    public function test_expired_item_rejected(): void
    {
        $expired = RedemptionItem::query()->create([
            'name' => 'Expired',
            'type' => RedemptionType::Merch,
            'points_cost' => 10,
            'stock' => null,
            'is_active' => true,
            'valid_until' => now()->subDay(),
        ]);

        $this->expectException(\App\Exceptions\RedemptionItemNotAvailableException::class);
        $this->redemptionService->redeem($this->parent, $this->player, $expired);
    }

    public function test_stock_decrements_never_oversells(): void
    {
        $limitedItem = RedemptionItem::query()->create([
            'name' => 'Limited',
            'type' => RedemptionType::Merch,
            'points_cost' => 10,
            'stock' => 2,
            'is_active' => true,
        ]);

        $this->redemptionService->redeem($this->parent, $this->player, $limitedItem);
        $this->assertSame(1, $limitedItem->fresh()->stock);

        $this->redemptionService->redeem($this->parent, $this->player, $limitedItem);
        $this->assertSame(0, $limitedItem->fresh()->stock);

        $this->expectException(\App\Exceptions\RedemptionItemNotAvailableException::class);
        $this->redemptionService->redeem($this->parent, $this->player, $limitedItem);
    }

    public function test_player_not_linked_rejected(): void
    {
        $unlinkedPlayer = Candidate::factory()->create([
            'season_id' => \App\Models\Season::query()->firstOrFail()->id,
            'is_player' => true,
        ]);

        $this->expectException(\App\Exceptions\PlayerNotLinkedException::class);
        $this->redemptionService->redeem($this->parent, $unlinkedPlayer, $this->item);
    }

    public function test_voucher_code_unique(): void
    {
        $redemption1 = $this->redemptionService->redeem($this->parent, $this->player, $this->item);
        $redemption2 = $this->redemptionService->redeem($this->parent, $this->player, $this->item);

        $this->assertNotSame($redemption1->voucher_code, $redemption2->voucher_code);
    }

    public function test_mark_fulfilled_sets_status_fulfilled_at_and_fulfilled_by(): void
    {
        $redemption = $this->redemptionService->redeem($this->parent, $this->player, $this->item);
        $admin = User::query()->firstOrFail();

        $redemption->update([
            'status' => RedemptionStatus::Fulfilled,
            'fulfilled_at' => now(),
            'fulfilled_by' => $admin->id,
        ]);

        $redemption->refresh();
        $this->assertSame(RedemptionStatus::Fulfilled, $redemption->status);
        $this->assertNotNull($redemption->fulfilled_at);
        $this->assertSame($admin->id, $redemption->fulfilled_by);
    }

    public function test_api_lists_catalog(): void
    {
        $response = $this->withToken($this->parentToken())
            ->getJson('/api/v1/redemption-items');

        $response->assertOk()
            ->assertJsonStructure(['data' => [
                ['id', 'name', 'description', 'type', 'points_cost', 'in_stock'],
            ]]);
    }

    public function test_api_redeem_success(): void
    {
        $response = $this->withToken($this->parentToken())
            ->postJson('/api/v1/redemptions', [
                'player_id' => $this->player->id,
                'redemption_item_id' => $this->item->id,
            ]);

        $response->assertOk()
            ->assertJsonStructure(['data' => [
                'id', 'voucher_code', 'points_spent', 'status', 'item', 'player_name', 'created_at',
            ]]);
    }

    public function test_api_redeem_insufficient_points_returns_422(): void
    {
        $expensiveItem = RedemptionItem::query()->create([
            'name' => 'Expensive',
            'type' => RedemptionType::Merch,
            'points_cost' => 99999,
            'stock' => null,
            'is_active' => true,
        ]);

        $response = $this->withToken($this->parentToken())
            ->postJson('/api/v1/redemptions', [
                'player_id' => $this->player->id,
                'redemption_item_id' => $expensiveItem->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_api_redeem_out_of_stock_returns_422(): void
    {
        $outOfStock = RedemptionItem::query()->create([
            'name' => 'OOS',
            'type' => RedemptionType::Merch,
            'points_cost' => 10,
            'stock' => 0,
            'is_active' => true,
        ]);

        $response = $this->withToken($this->parentToken())
            ->postJson('/api/v1/redemptions', [
                'player_id' => $this->player->id,
                'redemption_item_id' => $outOfStock->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_api_redeem_player_not_linked_returns_403(): void
    {
        $response = $this->withToken($this->parentToken())
            ->postJson('/api/v1/redemptions', [
                'player_id' => $this->otherPlayer->id,
                'redemption_item_id' => $this->item->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_api_redemption_history_isolation(): void
    {
        $otherParent = ParentAccount::factory()->create();

        $this->redemptionService->redeem($this->parent, $this->player, $this->item);

        $parentHistory = $this->withToken($this->parentToken())
            ->getJson('/api/v1/redemptions');

        $parentHistory->assertOk();
        $this->assertCount(1, $parentHistory->json('data'));

        $otherHistory = $this->actingAs($otherParent, 'sanctum')
            ->getJson('/api/v1/redemptions');

        $otherHistory->assertOk();
        $this->assertCount(0, $otherHistory->json('data'));
    }

    public function test_catalog_hides_out_of_stock_and_inactive_and_expired(): void
    {
        RedemptionItem::query()->create([
            'name' => 'Inactive',
            'type' => RedemptionType::Merch,
            'points_cost' => 10,
            'stock' => null,
            'is_active' => false,
        ]);
        RedemptionItem::query()->create([
            'name' => 'OOS',
            'type' => RedemptionType::Merch,
            'points_cost' => 10,
            'stock' => 0,
            'is_active' => true,
        ]);
        RedemptionItem::query()->create([
            'name' => 'Expired',
            'type' => RedemptionType::Merch,
            'points_cost' => 10,
            'stock' => null,
            'is_active' => true,
            'valid_until' => now()->subDay(),
        ]);

        $response = $this->withToken($this->parentToken())
            ->getJson('/api/v1/redemption-items');

        $names = collect($response->json('data'))->pluck('name');
        $this->assertContains('Test T-Shirt', $names);
        $this->assertNotContains('Inactive', $names);
        $this->assertNotContains('OOS', $names);
        $this->assertNotContains('Expired', $names);
    }
}
