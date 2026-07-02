<?php

namespace App\Models;

use App\Enums\DocumentStatus;
use App\Enums\FederationStatus;
use App\Enums\JoiningStatus;
use App\Enums\PlayingPosition;
use App\Enums\RecruitmentStage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'full_name',
    'year_of_birth',
    'date_of_birth',
    'country_of_birth',
    'citizenship',
    'year_arrived_qatar',
    'playing_position',
    'school',
    'previous_club',
    'parent_name',
    'parent_phone',
    'parent_whatsapp',
    'email',
    'notes',
    'season_id',
    'team_id',
    'is_player',
    'consent_given',
    'consent_at',
    'recruitment_stage',
    'document_status',
    'qfa_status',
    'fifa_status',
    'joining_status',
    'status_updated_at',
    'status_updated_by',
])]
class Candidate extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'is_player' => 'boolean',
            'consent_given' => 'boolean',
            'consent_at' => 'datetime',
            'recruitment_stage' => RecruitmentStage::class,
            'document_status' => DocumentStatus::class,
            'qfa_status' => FederationStatus::class,
            'fifa_status' => FederationStatus::class,
            'joining_status' => JoiningStatus::class,
            'status_updated_at' => 'datetime',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function statusUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(CandidateStatusHistory::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CandidateDocument::class);
    }

    public function pointTransactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class, 'candidate_id');
    }

    public function pointsBalance(): int
    {
        return (int) $this->pointTransactions()->sum('points');
    }

    public function parentAccounts(): BelongsToMany
    {
        return $this->belongsToMany(ParentAccount::class, 'parent_player_links')
            ->withTimestamps();
    }

    public function canBeMarkedAsPlayer(): bool
    {
        return $this->recruitment_stage === RecruitmentStage::Accepted && ! $this->is_player;
    }

    public function publicProgressLabel(): string
    {
        if ($this->joining_status === JoiningStatus::JoinedTeam) {
            return __('enums.progress.joined');
        }

        if ($this->document_status !== DocumentStatus::Complete) {
            return __('enums.progress.documents_required');
        }

        if ($this->recruitment_stage === RecruitmentStage::Accepted) {
            return __('enums.progress.accepted');
        }

        return $this->recruitment_stage->getLabel() ?? __('enums.progress.in_progress');
    }

    public function playingPositionLabel(): string
    {
        return PlayingPosition::from($this->getAttribute('playing_position'))->getLabel() ?? (string) $this->getAttribute('playing_position');
    }
}
