<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'website',
        'jobs_page',
        'remote',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function occupancies(): HasManyThrough
    {
        return $this->hasManyThrough(Occupancy::class, Position::class);
    }

    protected function casts(): array
    {
        return [
            'remote' => 'boolean',
        ];
    }
}
