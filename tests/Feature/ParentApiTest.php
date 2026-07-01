<?php

namespace Tests\Feature;

use App\Enums\PointTransactionType;
use App\Models\Candidate;
use App\Models\ParentAccount;
use App\Models\PointTransaction;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_and_wrong_password_is_rejected(): void
    {
        $this->seed();

        $parent = ParentAccount::factory()->create([
            'email' => 'parent1@example.com',
            'password' => 'secret123',
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $parent->email,
            'password' => 'wrong-password',
        ])->assertUnprocessable();

        $this->postJson('/api/v1/auth/login', [
            'email' => $parent->email,
            'password' => 'secret123',
        ])->assertOk()
            ->assertJsonStructure([
                'token',
                'parent' => ['id', 'name', 'email', 'phone', 'whatsapp', 'invited_at', 'accepted_at'],
            ]);
    }

    public function test_accept_invite_sets_password_and_returns_token_and_invalid_token_is_rejected(): void
    {
        $this->seed();

        $parent = ParentAccount::factory()->invited()->create([
            'email' => 'invite@example.com',
            'invitation_token' => 'invite-token',
        ]);

        $this->postJson('/api/v1/auth/accept-invite', [
            'token' => 'bad-token',
            'password' => 'secret123',
        ])->assertUnprocessable();

        $this->postJson('/api/v1/auth/accept-invite', [
            'token' => 'invite-token',
            'password' => 'secret123',
        ])->assertOk()
            ->assertJsonStructure([
                'token',
                'parent' => ['id', 'name', 'email'],
            ]);

        $parent->refresh();

        $this->assertNotNull($parent->accepted_at);
        $this->assertNull($parent->invitation_token);
        $this->assertNotNull($parent->password);
    }

    public function test_me_and_players_require_authentication(): void
    {
        $this->seed();

        $this->getJson('/api/v1/me')->assertUnauthorized();
        $this->getJson('/api/v1/players')->assertUnauthorized();
    }

    public function test_parent_only_sees_their_own_players_and_cannot_read_others(): void
    {
        $this->seed();

        [$parentA, $playerA] = $this->createParentWithLinkedPlayer('parent-a@example.com', 'Player A');
        [$parentB, $playerB] = $this->createParentWithLinkedPlayer('parent-b@example.com', 'Player B');

        $tokenA = $parentA->createToken('mobile')->plainTextToken;

        $this->withToken($tokenA)
            ->getJson('/api/v1/players')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $playerA->id);

        $this->withToken($tokenA)
            ->getJson("/api/v1/players/{$playerA->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $playerA->id);

        $this->withToken($tokenA)
            ->getJson("/api/v1/players/{$playerB->id}")
            ->assertNotFound();
    }

    public function test_player_resource_hides_internal_candidate_fields(): void
    {
        $this->seed();

        [$parent, $player] = $this->createParentWithLinkedPlayer('safe-output@example.com', 'Safe Output');
        $token = $parent->createToken('mobile')->plainTextToken;

        $this->withToken($token)
            ->getJson("/api/v1/players/{$player->id}")
            ->assertOk()
            ->assertJsonMissingPath('data.notes')
            ->assertJsonMissingPath('data.status_updated_by')
            ->assertJsonMissingPath('data.parent_phone')
            ->assertJsonStructure([
                'data' => ['id', 'full_name', 'playing_position', 'team_name', 'points_balance', 'is_player', 'progress'],
            ]);
    }

    public function test_players_api_includes_points_balance_from_ledger_sum(): void
    {
        $this->seed();

        [$parent, $player] = $this->createParentWithLinkedPlayer('balance@example.com', 'Balance Player');
        $token = $parent->createToken('mobile')->plainTextToken;

        PointTransaction::query()->create([
            'candidate_id' => $player->id,
            'type' => PointTransactionType::Earn,
            'points' => 80,
        ]);

        PointTransaction::query()->create([
            'candidate_id' => $player->id,
            'type' => PointTransactionType::Redeem,
            'points' => -25,
        ]);

        $this->withToken($token)
            ->getJson("/api/v1/players/{$player->id}")
            ->assertOk()
            ->assertJsonPath('data.points_balance', 55);
    }

    public function test_player_transactions_endpoint_returns_only_linked_players_rows(): void
    {
        $this->seed();

        [$parent, $player] = $this->createParentWithLinkedPlayer('history@example.com', 'History Player');
        [, $otherPlayer] = $this->createParentWithLinkedPlayer('other-history@example.com', 'Other History');
        $token = $parent->createToken('mobile')->plainTextToken;

        PointTransaction::query()->create([
            'candidate_id' => $player->id,
            'type' => PointTransactionType::Earn,
            'points' => 20,
            'reason' => 'Joined loyalty',
        ]);

        $this->travel(2)->seconds();

        PointTransaction::query()->create([
            'candidate_id' => $player->id,
            'type' => PointTransactionType::Redeem,
            'points' => -10,
            'reason' => 'Voucher issued',
        ]);

        PointTransaction::query()->create([
            'candidate_id' => $otherPlayer->id,
            'type' => PointTransactionType::Earn,
            'points' => 99,
            'reason' => 'Other player',
        ]);

        $this->withToken($token)
            ->getJson("/api/v1/players/{$player->id}/transactions")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.points', -10)
            ->assertJsonPath('data.0.type', PointTransactionType::Redeem->value)
            ->assertJsonPath('data.0.reason', 'Voucher issued')
            ->assertJsonPath('data.0.source', null)
            ->assertJsonPath('data.1.points', 20)
            ->assertJsonPath('data.1.type', PointTransactionType::Earn->value)
            ->assertJsonPath('data.1.reason', 'Joined loyalty');
    }

    public function test_account_transactions_endpoint_returns_only_account_rows(): void
    {
        $this->seed();

        [$parent, $player] = $this->createParentWithLinkedPlayer('account-history@example.com', 'Linked Player');
        $token = $parent->createToken('mobile')->plainTextToken;

        PointTransaction::query()->create([
            'parent_account_id' => $parent->id,
            'type' => PointTransactionType::Adjust,
            'points' => 150,
            'reason' => 'Manual grant',
        ]);

        PointTransaction::query()->create([
            'candidate_id' => $player->id,
            'type' => PointTransactionType::Earn,
            'points' => 30,
            'reason' => 'Player only',
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/me/transactions')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.points', 150)
            ->assertJsonPath('data.0.type', PointTransactionType::Adjust->value)
            ->assertJsonPath('data.0.reason', 'Manual grant');
    }

    public function test_player_transactions_404_for_non_linked_player(): void
    {
        $this->seed();

        [$parent] = $this->createParentWithLinkedPlayer('reader@example.com', 'Reader Player');
        [, $otherPlayer] = $this->createParentWithLinkedPlayer('other-reader@example.com', 'Other Reader');
        $token = $parent->createToken('mobile')->plainTextToken;

        $this->withToken($token)
            ->getJson("/api/v1/players/{$otherPlayer->id}/transactions")
            ->assertNotFound();
    }

    /**
     * @return array{0: ParentAccount, 1: Candidate}
     */
    private function createParentWithLinkedPlayer(string $email, string $playerName): array
    {
        $season = Season::query()->firstOrFail();
        $team = Team::query()->firstOrFail();

        $player = Candidate::factory()->create([
            'full_name' => $playerName,
            'season_id' => $season->id,
            'team_id' => $team->id,
            'is_player' => true,
            'email' => $email,
            'recruitment_stage' => 'accepted',
            'document_status' => 'complete',
            'joining_status' => 'ready_to_join',
        ]);

        $parent = ParentAccount::factory()->create([
            'email' => $email,
        ]);

        $parent->players()->attach($player);

        return [$parent, $player];
    }
}
