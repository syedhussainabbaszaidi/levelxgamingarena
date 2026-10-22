<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'description',
        'subject_type', 'subject_id',
        'ip_address', 'user_agent', 'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeFilter(Builder $q, array $filters): Builder
    {
        return $q
            ->when($filters['q'] ?? null, function (Builder $qq, string $term) {
                $qq->where(function (Builder $w) use ($term) {
                    $w->where('description', 'like', "%{$term}%")
                        ->orWhere('action', 'like', "%{$term}%")
                        ->orWhere('ip_address', 'like', "%{$term}%");
                });
            })
            ->when($filters['action'] ?? null, fn (Builder $qq, $a) => $qq->where('action', $a))
            ->when($filters['user'] ?? null, fn (Builder $qq, $u) => $qq->where('user_id', $u))
            ->when($filters['group'] ?? null, fn (Builder $qq, $g) => $qq->where('action', 'like', $g.'.%'))
            ->when($filters['from'] ?? null, fn (Builder $qq, $d) => $qq->whereDate('created_at', '>=', $d))
            ->when($filters['to'] ?? null, fn (Builder $qq, $d) => $qq->whereDate('created_at', '<=', $d));
    }

    /** Human readable prefix of the action, e.g. "booking". */
    public function group(): string
    {
        return explode('.', $this->action)[0];
    }

    /** Tailwind text colour used for the action badge. */
    public function tone(): string
    {
        return match (true) {
            str_contains($this->action, 'deleted'), str_contains($this->action, 'cancelled') => 'text-rose-400',
            str_contains($this->action, 'created') => 'text-emerald-400',
            str_contains($this->action, 'login'), str_contains($this->action, 'logout') => 'text-sky-400',
            str_contains($this->action, 'updated'), str_contains($this->action, 'changed') => 'text-amber-400',
            default => 'text-zinc-300',
        };
    }
}
