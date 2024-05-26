<?php

declare(strict_types=1);

namespace App\Commands\App;

use App\Concerns\WithMiddleware;
use App\Middleware\Guest;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Laravel\Prompts as P;
use Laravel\Prompts\FormBuilder;
use LaravelZero\Framework\Commands\Command;

class RegisterCommand extends Command
{
    use WithMiddleware;

    protected $signature = 'register';

    protected $description = 'Register a new user.';

    public function handle(): int
    {
        try {
            return $this->withMiddleware([Guest::class], function (): int {
                info('Register a new user');

                $responses = $this->form()->submit();

                $user = $this->createUser($responses);

                $this->displaySuccessMessage($user);

                return Command::SUCCESS;
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
                label: 'Name',
                placeholder: 'e.g. John Doe',
                required: true,
                validate: ['name' => 'max:255'],
                name: 'name',
            )
            ->text(
                label: 'Email',
                placeholder: 'e.g. john@doe.com',
                required: true,
                validate: ['email' => 'email|unique:users,email|max:255'],
                name: 'email',
            )
            ->password(
                label: 'Password',
                placeholder: 'e.g. password',
                required: true,
                validate: ['password' => 'min:8'],
                name: 'password',
            );
    }

    private function createUser(array $data): User
    {
        $attributes = [
            'name' => $data['name'],
            'email' => str()->lower($data['email']),
            'password' => Hash::make($data['password']),
        ];

        return User::create($attributes);
    }

    private function displaySuccessMessage(User $user): void
    {
        P\info(sprintf(
            "User '%s' with email '%s' was registered!",
            $user->name,
            $user->email,
        ));

        P\info("You can now log in using the 'login' command.");
    }
}
