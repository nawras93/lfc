<?php

namespace App\Services;

use App\Enums\PointTransactionType;
use App\Models\Candidate;
use App\Models\Fixture;
use App\Models\PointRule;
use App\Models\PointTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PointsEngine
{
    public function resolveRule(Fixture $fixture, ?Carbon $at = null): ?PointRule
    {
        $at ??= now();

        return PointRule::query()
            ->activeOn()
            ->forFixture($fixture)
            ->where(function ($q) use ($at) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $at);
            })
            ->where(function ($q) use ($at) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $at);
            })
            ->orderByRaw('CASE WHEN team_id IS NOT NULL THEN 0 ELSE 1 END')
            ->orderByRaw('CASE WHEN season_id IS NOT NULL THEN 0 ELSE 1 END')
            ->orderBy('priority', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }

    public function credit(Candidate $player, Fixture $fixture, ?Model $source = null, ?Carbon $at = null): ?PointTransaction
    {
        $rule = $this->resolveRule($fixture, $at);

        if ($rule === null) {
            return null;
        }

        return PointTransaction::query()->create([
            'candidate_id' => $player->id,
            'type' => PointTransactionType::Earn,
            'points' => $rule->pointsValue(),
            'point_rule_id' => $rule->id,
            'source_type' => $source?->getMorphClass(),
            'source_id' => $source?->getKey(),
            'created_by' => null,
        ]);
    }

    public function adjust(Candidate $player, int $points, string $reason, User $by, string $type = 'adjust'): PointTransaction
    {
        return PointTransaction::query()->create([
            'candidate_id' => $player->id,
            'type' => PointTransactionType::from($type),
            'points' => $points,
            'point_rule_id' => null,
            'reason' => $reason,
            'created_by' => $by->id,
        ]);
    }
}
