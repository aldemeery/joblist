<?php

declare(strict_types=1);

namespace App\Models;

use App\ValueObjects\Address;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'date',
        'net_terms',
        'due_date',
        'issuer_address_line_one',
        'issuer_address_line_two',
        'issuer_address_line_three',
        'issuer_address_line_four',
        'client_address_line_one',
        'client_address_line_two',
        'client_address_line_three',
        'client_address_line_four',
        'tax',
        'discount',
        'payment_details',
        'notes',
    ];

    public function occupancy(): BelongsTo
    {
        return $this->belongsTo(Occupancy::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function taxAmount(): float
    {
        return round(
            $this->subtotal() * $this->tax / 100,
            2,
        );
    }

    public function subtotal(): float
    {
        $this->load('items');

        return round(
            $this->items->sum(fn (InvoiceItem $item): float => $item->amount()),
            2,
        );
    }

    public function total(): float
    {
        return round($this->subtotal() + $this->taxAmount() - $this->discount, 2);
    }

    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'date' => 'date',
            'net_terms' => 'integer',
            'due_date' => 'date',
            'client_address' => Address::class,
            'issuer_address' => Address::class,
            'tax' => 'float',
            'discount' => 'float',
        ];
    }
}
