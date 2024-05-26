<?php

declare(strict_types=1);

return [
    'name' => 'Joblist',
    'version' => app('git.version'),
    'env' => 'development',
    'providers' => [
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
    ],
];
