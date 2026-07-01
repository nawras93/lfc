<?php

namespace Database\Seeders;

use App\Enums\AccountType;
use App\Enums\DocumentStatus;
use App\Enums\FederationStatus;
use App\Enums\JoiningStatus;
use App\Enums\PlayingPosition;
use App\Enums\PointTransactionType;
use App\Enums\RecruitmentStage;
use App\Enums\RedemptionStatus;
use App\Models\Candidate;
use App\Models\ParentAccount;
use App\Models\PointTransaction;
use App\Models\Redemption;
use App\Models\RedemptionItem;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Extra demo data layered on top of the core walkthrough (Amina/Omar + VVIP):
 *
 *  - A parent (Fatima) with TWO players on different age groups, different
 *    balances, and both a pending and a fulfilled voucher.
 *  - A candidate still in the recruitment pipeline (not yet a player) so the
 *    admin Candidates board shows a realistic funnel.
 *
 * Idempotent (updateOrCreate/firstOrCreate) and additive — it never touches the
 * numbers the walkthrough depends on (Omar 150, VVIP 500).
 */
class DemoScenariosSeeder extends Seeder
{
    public function run(): void
    {
        $season = Season::query()->first();
        $u12 = Team::query()->where('name', 'LFC U12')->first();
        $u14 = Team::query()->where('name', 'LFC U14')->first();

        if (! $season || ! $u12 || ! $u14) {
            return;
        }

        $admin = User::query()->first();

        // --- A parent with more than one player -----------------------------
        $parent = ParentAccount::query()->updateOrCreate(
            ['email' => 'parent2.demo@lfc.test'],
            [
                'name' => 'Fatima Al-Kuwari',
                'password' => 'password',
                'phone' => '555110110',
                'whatsapp' => '555110111',
                'is_vvip' => false,
                'account_type' => AccountType::Parent,
                'accepted_at' => now(),
                'invitation_token' => null,
                'invited_at' => now(),
            ],
        );

        $yousef = $this->player(
            email: 'yousef.demo@lfc.test',
            name: 'Yousef Al-Kuwari',
            parentName: 'Fatima Al-Kuwari',
            phone: '555110110',
            whatsapp: '555110111',
            yearOfBirth: 2012,
            dob: '2012-09-02',
            position: PlayingPosition::Attacker,
            season: $season,
            team: $u14,
            joining: JoiningStatus::JoinedTeam,
        );

        $hassan = $this->player(
            email: 'hassan.demo@lfc.test',
            name: 'Hassan Al-Kuwari',
            parentName: 'Fatima Al-Kuwari',
            phone: '555110110',
            whatsapp: '555110111',
            yearOfBirth: 2014,
            dob: '2014-11-20',
            position: PlayingPosition::Defender,
            season: $season,
            team: $u12,
            joining: JoiningStatus::ReadyToJoin,
        );

        $parent->players()->syncWithoutDetaching([$yousef->id, $hassan->id]);

        // Starting balances: Yousef 300, Hassan 120.
        $this->grantStartingPoints($yousef, 300);
        $this->grantStartingPoints($hassan, 120);

        // Yousef redeems a Training Kit Bundle (80) → left ISSUED so the
        // "Pending fulfillments" widget and "Mark fulfilled" action have more
        // than one row to demo. Net balance 220.
        $this->seedRedemption(
            voucher: 'DEMO-YOUSEF-ISSUED',
            parent: $parent,
            player: $yousef,
            itemName: 'Training Kit Bundle',
            status: RedemptionStatus::Issued,
            admin: $admin,
        );

        // Hassan redeems a Water Bottle (20) → already FULFILLED, showing a
        // second fulfilled voucher in history. Net balance 100.
        $this->seedRedemption(
            voucher: 'DEMO-HASSAN-FULFILLED',
            parent: $parent,
            player: $hassan,
            itemName: 'Water Bottle',
            status: RedemptionStatus::Fulfilled,
            admin: $admin,
        );

        // --- A candidate still in the recruitment pipeline ------------------
        // Not a player yet, no parent link, no points — populates the admin
        // Candidates funnel with a mid-pipeline record.
        Candidate::query()->updateOrCreate(
            ['email' => 'tariq.trial@lfc.test'],
            [
                'full_name' => 'Tariq Al-Ansari',
                'year_of_birth' => 2013,
                'date_of_birth' => '2013-06-18',
                'country_of_birth' => 'Qatar',
                'citizenship' => 'Qatari',
                'year_arrived_qatar' => 2013,
                'playing_position' => PlayingPosition::Midfielder,
                'school' => 'Al Wakrah Independent School',
                'previous_club' => 'Aspire Academy',
                'parent_name' => 'Mariam Al-Ansari',
                'parent_phone' => '555120120',
                'parent_whatsapp' => '555120121',
                'notes' => 'Trial invitee — assessment scheduled, documents in progress.',
                'season_id' => $season->id,
                'team_id' => $u14->id,
                'is_player' => false,
                'consent_given' => true,
                'consent_at' => now(),
                'recruitment_stage' => RecruitmentStage::AssessmentScheduled,
                'document_status' => DocumentStatus::InProgress,
                'qfa_status' => FederationStatus::Submitted->value,
                'fifa_status' => FederationStatus::NotStarted->value,
                'joining_status' => JoiningStatus::NotStarted,
            ],
        );
    }

    private function player(
        string $email,
        string $name,
        string $parentName,
        string $phone,
        string $whatsapp,
        int $yearOfBirth,
        string $dob,
        PlayingPosition $position,
        Season $season,
        Team $team,
        JoiningStatus $joining,
    ): Candidate {
        return Candidate::query()->updateOrCreate(
            ['email' => $email],
            [
                'full_name' => $name,
                'year_of_birth' => $yearOfBirth,
                'date_of_birth' => $dob,
                'country_of_birth' => 'Qatar',
                'citizenship' => 'Qatari',
                'year_arrived_qatar' => $yearOfBirth,
                'playing_position' => $position,
                'school' => 'Lusail Demo School',
                'previous_club' => 'None',
                'parent_name' => $parentName,
                'parent_phone' => $phone,
                'parent_whatsapp' => $whatsapp,
                'notes' => 'Seeded demo player (multi-child family).',
                'season_id' => $season->id,
                'team_id' => $team->id,
                'is_player' => true,
                'consent_given' => true,
                'consent_at' => now(),
                'recruitment_stage' => RecruitmentStage::Accepted,
                'document_status' => DocumentStatus::Complete,
                'qfa_status' => FederationStatus::NotStarted->value,
                'fifa_status' => FederationStatus::NotStarted->value,
                'joining_status' => $joining,
            ],
        );
    }

    private function grantStartingPoints(Candidate $player, int $points): void
    {
        PointTransaction::query()->firstOrCreate(
            [
                'candidate_id' => $player->id,
                'type' => PointTransactionType::Adjust,
                'reason' => 'Demo account — starting points',
            ],
            [
                'points' => $points,
            ],
        );
    }

    private function seedRedemption(
        string $voucher,
        ParentAccount $parent,
        Candidate $player,
        string $itemName,
        RedemptionStatus $status,
        ?User $admin,
    ): void {
        $item = RedemptionItem::query()->where('name', $itemName)->first();

        if ($item === null) {
            return;
        }

        $fulfilled = $status === RedemptionStatus::Fulfilled;

        $redemption = Redemption::query()->updateOrCreate(
            ['voucher_code' => $voucher],
            [
                'parent_account_id' => $parent->id,
                'candidate_id' => $player->id,
                'redemption_item_id' => $item->id,
                'points_spent' => $item->points_cost,
                'status' => $status,
                'fulfilled_at' => $fulfilled ? now()->subDay() : null,
                'fulfilled_by' => $fulfilled ? $admin?->id : null,
            ],
        );

        PointTransaction::query()->firstOrCreate(
            [
                'candidate_id' => $player->id,
                'type' => PointTransactionType::Redeem,
                'source_type' => $redemption->getMorphClass(),
                'source_id' => $redemption->id,
            ],
            [
                'points' => -1 * $item->points_cost,
                'reason' => 'Voucher issued',
            ],
        );
    }
}
