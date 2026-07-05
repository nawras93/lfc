<?php

namespace App\Models;

use App\Enums\AppKey;
use App\Enums\AccountType;
use App\Enums\LedgerUnit;
use App\Models\Concerns\ScopedToApp;
use Database\Factories\ParentAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name',
    'email',
    'password',
    'phone',
    'whatsapp',
    'invitation_token',
    'invited_at',
    'accepted_at',
    'is_vvip',
    'account_type',
    'app',
])]
#[Hidden([
    'password',
    'remember_token',
    'invitation_token',
])]
class ParentAccount extends Authenticatable
{
    /** @use HasFactory<ParentAccountFactory> */
    use HasApiTokens, HasFactory, Notifiable, ScopedToApp;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'invited_at' => 'datetime',
            'accepted_at' => 'datetime',
            'is_vvip' => 'boolean',
            'account_type' => AccountType::class,
            'app' => AppKey::class,
        ];
    }

    public function isVvipClient(): bool
    {
        return $this->account_type === AccountType::VvipClient;
    }

    public function isMember(): bool
    {
        return $this->account_type === AccountType::Member;
    }

    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Candidate::class, 'parent_player_links')
            ->withTimestamps();
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(Redemption::class);
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class, 'parent_account_id');
    }

    public function pointsBalance(): int
    {
        return (int) $this->pointTransactions()
            ->where('unit', LedgerUnit::Points->value)
            ->sum('points');
    }

    public function discountBalance(): int
    {
        return (int) $this->pointTransactions()
            ->where('unit', LedgerUnit::DiscountPct->value)
            ->sum('points');
    }

    public function discountPercent(): float
    {
        return $this->discountBalance() / 100;
    }

    public function syncPlayers(array $candidateIds): void
    {
        $playerIds = Candidate::query()
            ->whereIn('id', $candidateIds)
            ->where('is_player', true)
            ->pluck('id')
            ->all();

        if (count($playerIds) !== count(array_unique($candidateIds))) {
            throw new \InvalidArgumentException('Parents can only be linked to candidates marked as players.');
        }

        $this->players()->sync($playerIds);
    }
}
