<?php

declare(strict_types=1);

namespace App\Enums;

enum Currency: string
{
    case AUD = 'AUD';
    case CAD = 'CAD';
    case EGP = 'EGP';
    case EUR = 'EUR';
    case GBP = 'GBP';
    case USD = 'USD';

    public function symbol(): string
    {
        return match ($this) {
            Currency::AUD => '$',
            Currency::CAD => '$',
            Currency::EGP => '£',
            Currency::EUR => '€',
            Currency::GBP => '£',
            Currency::USD => '$',
        };
    }
}
