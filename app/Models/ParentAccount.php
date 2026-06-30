<?php

namespace App\Models;

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
])]
#[Hidden([
    'password',
    'remember_token',
    'invitation_token',
])]
class ParentAccount extends Authenticatable
{
    /** @use HasFactory<ParentAccountFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'invited_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
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
