<?php

declare(strict_types=1);

namespace App\Concerns;

use Closure;
use Illuminate\Support\Facades\Pipeline;

trait WithMiddleware
{
    public function withMiddleware(array $middlewares, Closure $handle): int
    {
        return Pipeline::send($this)
            ->through($middlewares)
            ->then($handle);
    }
}
