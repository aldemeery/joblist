<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;

class InvoiceThemer
{
    private static array $themes = [];

    public static function registerTheme(string $name, string $path): void
    {
        if (!View::exists($path)) {
            throw new InvalidArgumentException(sprintf(
                "View '%s' could not be found!",
                $path,
            ));
        }

        static::$themes[$name] = $path;
    }

    public static function make(string $name, Invoice $invoice): Renderable
    {
        if (!isset(static::$themes[$name])) {
            throw new InvalidArgumentException(sprintf(
                "Theme '%s' could not be found",
                $name,
            ));
        }

        return View::make(static::$themes[$name], compact('invoice'));
    }
}
