<?php

declare(strict_types=1);

namespace App\Commands\App;

use App\Concerns\WithMiddleware;
use App\Middleware\Guest;
use Exception;
use Laravel\Prompts as P;
use Laravel\Prompts\FormBuilder;
use LaravelZero\Framework\Commands\Command;

class LoginCommand extends Command
{
    use WithMiddleware;

    protected $signature = 'login';

    protected $description = 'Log in.';

    public function handle(): int
    {
        try {
            return $this->withMiddleware([Guest::class], function (): int {
                P\info('Log in');

                $responses = $this->form()->submit();

                if (auth()->guard()->attempt($responses)) {
                    P\info('You have been logged in successfully.');

                    return Command::SUCCESS;
                }

                P\error('Invalid credentials.');

                return Command::FAILURE;
            });
        } catch (Exception $e) {
            P\error($e->getMessage());

            return Command::FAILURE;
        }
    }

    private function form(): FormBuilder
    {
        return P\form()
            ->text(
                label: 'Email',
                placeholder: 'e.g. john@doe.com',
                required: true,
                validate: ['email' => 'email|max:255'],
                name: 'email',
            )
            ->password(
                label: 'Password',
                placeholder: 'e.g. password',
                required: true,
                name: 'password',
            );
    }
}
