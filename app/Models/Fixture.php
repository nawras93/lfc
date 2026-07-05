<?php

namespace App\Models;

use App\Enums\AppKey;
use App\Enums\FixtureStatus;
use App\Models\Concerns\ScopedToApp;
use App\Support\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'team_id',
    'season_id',
    'opponent',
    'opponent_ar',
    'competition',
    'competition_ar',
    'is_home',
    'venue',
    'kickoff_at',
    'scan_opens_at',
    'scan_closes_at',
    'status',
    'our_score',
    'opponent_score',
    'app',
])]
class Fixture extends Model
{
    use HasFactory, HasLocalizedContent, ScopedToApp, SoftDeletes;

    protected function casts(): array
    {
        return [
            'kickoff_at' => 'datetime',
            'scan_opens_at' => 'datetime',
            'scan_closes_at' => 'datetime',
            'status' => FixtureStatus::class,
            'is_home' => 'boolean',
            'our_score' => 'integer',
            'opponent_score' => 'integer',
            'app' => AppKey::class,
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function isOpenForScanning(): bool
    {
        return $this->status === FixtureStatus::OpenForScanning
            && ($this->scan_opens_at === null || now() >= $this->scan_opens_at)
            && ($this->scan_closes_at === null || now() <= $this->scan_closes_at);
    }

    public function isPlayed(): bool
    {
        return $this->our_score !== null && $this->opponent_score !== null;
    }

    public function isUpcoming(): bool
    {
        return ! $this->isPlayed() && $this->kickoff_at?->isFuture();
    }
}
