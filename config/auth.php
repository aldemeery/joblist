<?php

declare(strict_types=1);

return [
    'defaults' => [
        'guard' => 'console',
        'passwords' => 'users',
        'provider' => 'users',
    ],
    'guards' => [
        'console' => [
            'driver' => 'cache',
            'provider' => 'users',
            'timeout' => env('AUTH_TIMEOUT', 60 * 60 * 24 * 3), // 3 days by default.
        ],
    ],
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
    ],
];
