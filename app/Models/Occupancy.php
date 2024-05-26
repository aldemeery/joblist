<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Occupancy extends Pivot
{
    use HasFactory;

    public $incrementing = true;

    protected $table = 'occupancies';

    protected $foreignKey = 'occupancy_id';

    protected $fillable = [
        'salary_amount',
        'salary_currency',
        'starred',
        'started_at',
        'ended_at',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    protected function casts(): array
    {
        return [
            'salary_amount' => 'float',
            'salary_currency' => Currency::class,
            'starred' => 'boolean',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }
}
