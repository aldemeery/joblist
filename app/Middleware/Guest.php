<?php

declare(strict_types=1);

namespace App\Middleware;

use Closure;
use Exception;
use LaravelZero\Framework\Commands\Command;

class Guest
{
    public function handle(Command $command, Closure $next): int
    {
        if (auth()->check()) {
            throw new Exception(
                "A user is already logged in! To log out use the 'logout' command.",
            );
        }

        return $next($command);
    }
}
