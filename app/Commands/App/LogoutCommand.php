<?php

declare(strict_types=1);

namespace App\Commands\App;

use App\Concerns\WithMiddleware;
use App\Middleware\Auth;
use Exception;
use Laravel\Prompts as P;
use LaravelZero\Framework\Commands\Command;

class LogoutCommand extends Command
{
    use WithMiddleware;

    protected $signature = 'logout';

    protected $description = 'Log out.';

    public function handle(): int
    {
        try {
            return $this->withMiddleware([Auth::class], function (): int {
                auth()->guard()->logout();

                P\info('You have been logged out successfully.');

                return Command::SUCCESS;
            });
        } catch (Exception $e) {
            P\error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
