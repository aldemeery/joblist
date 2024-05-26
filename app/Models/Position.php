<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'posting_url',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function occupancies(): HasMany
    {
        return $this->hasMany(Occupancy::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, Occupancy::class)
            ->as('occupancy')
            ->using(Occupancy::class)
            ->withPivot(['salary_amount', 'salary_currency', 'starred', 'started_at', 'ended_at'])
            ->withTimestamps();
    }
}
