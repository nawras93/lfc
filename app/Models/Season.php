<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'is_active', 'registration_slug', 'registration_starts_at', 'registration_ends_at'])]
class Season extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        // Generate the public registration token on any save when it's missing,
        // so a season can't end up without one (a null token breaks the public
        // registration link). `saving` (not `creating`) means an existing
        // token-less season self-heals the next time it's saved in the admin.
        static::saving(function (Season $season): void {
            if (blank($season->registration_slug)) {
                $season->registration_slug = Str::lower(Str::random(16));
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'registration_starts_at' => 'datetime',
            'registration_ends_at' => 'datetime',
        ];
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function registrationSeasonSlug(): string
    {
        // Fall back to a non-empty segment so a symbol-only name can't slug to ''
        // and throw UrlGenerationException when building the public registration URL.
        return Str::slug($this->name) ?: 'season';
    }

    public function publicRegistrationUrl(): ?string
    {
        if (blank($this->registration_slug)) {
            return null;
        }

        return route('public.register.show', [
            'seasonSlug' => $this->registrationSeasonSlug(),
            'registrationSlug' => $this->registration_slug,
        ]);
    }

    public function registrationIsOpen(): bool
    {
        if ($this->registration_starts_at === null || $this->registration_ends_at === null) {
            return false;
        }

        $now = now();

        return $now->between($this->registration_starts_at, $this->registration_ends_at);
    }
}
