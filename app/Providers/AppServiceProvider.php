<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\InvoiceThemer;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Silence is golden...
    }

    public function register(): void
    {
        InvoiceThemer::registerTheme('default', 'invoices.default');

        if ($this->app->environment('development')) {
            $this->app->register(\Intonate\TinkerZero\TinkerZeroServiceProvider::class);
        }
    }
}
