<?php

declare(strict_types=1);

namespace App\Middleware;

use Closure;
use Exception;
use LaravelZero\Framework\Commands\Command;

class Auth
{
    public function handle(Command $command, Closure $next): int
    {
        if (!auth()->check()) {
            throw new Exception(
                "No users are logged in! To log in use the 'login' command.",
            );
        }

        return $next($command);
    }
}
