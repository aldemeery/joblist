<?php

declare(strict_types=1);

namespace App\Providers;

use App\Guards\CacheGuard;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\CreatesUserProviders;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    use CreatesUserProviders;

    public function boot(): void
    {
        Auth::extend('cache', function (): Guard {
            return new CacheGuard(function (UserProvider $provider): ?Authenticatable {
                return $provider->retrieveById(Cache::get('logged_in_user_id'));
            }, $this->app['cache.store'], $this->createUserProvider());
        });
    }

    public function register(): void
    {
        // Silence is golden...
    }
}
