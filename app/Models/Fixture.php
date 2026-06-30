<?php

namespace App\Models;

use App\Enums\FixtureStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'team_id',
    'season_id',
    'opponent',
    'venue',
    'kickoff_at',
    'scan_opens_at',
    'scan_closes_at',
    'status',
])]
class Fixture extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'kickoff_at' => 'datetime',
            'scan_opens_at' => 'datetime',
            'scan_closes_at' => 'datetime',
            'status' => FixtureStatus::class,
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
}
