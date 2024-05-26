<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Model;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function occupancies(): HasMany
    {
        return $this->hasMany(Occupancy::class);
    }

    public function positions(): BelongsToMany
    {
        return $this->belongsToMany(Position::class, Occupancy::class)
            ->as('occupancy')
            ->using(Occupancy::class)
            ->withPivot(['salary_amount', 'salary_currency', 'starred', 'started_at', 'ended_at'])
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
