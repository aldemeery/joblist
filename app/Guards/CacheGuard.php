<?php

declare(strict_types=1);

namespace App\Guards;

use Closure;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Traits\Macroable;

class CacheGuard implements StatefulGuard
{
    use GuardHelpers;
    use Macroable;

    private ?Authenticatable $lastAttempted = null;

    private bool $loggedOut = false;

    public function __construct(
        private Closure $callback,
        private Repository $cache,
        ?UserProvider $provider = null
    ) {
        $this->provider = $provider;
    }

    public function user()
    {
        if ($this->loggedOut) {
            return;
        }

        if (!is_null($this->user)) {
            return $this->user;
        }

        $id = $this->cache->get($this->getName());

        $this->user = $this->provider->retrieveById($id);

        return $this->user;
    }

    public function validate(array $credentials = [])
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        return $this->hasValidCredentials($user, $credentials);
    }

    public function attempt(array $credentials = [], $remember = false)
    {
        $this->lastAttempted = $user = $this->provider->retrieveByCredentials($credentials);

        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);

            return true;
        }

        return false;
    }

    public function once(array $credentials = [])
    {
        if ($this->validate($credentials)) {
            $this->setUser($this->lastAttempted);

            return true;
        }

        return false;
    }

    public function login(Authenticatable $user, $remember = false): void
    {
        $this->cache->put($this->getName(), $user->getAuthIdentifier(), $this->loginTimeInSeconds());

        $this->setUser($user);
    }

    public function loginUsingId($id, $remember = false)
    {
        if (!is_null($user = $this->provider->retrieveById($id))) {
            $this->login($user, $remember);

            return $user;
        }

        return false;
    }

    public function onceUsingId($id)
    {
        if (!is_null($user = $this->provider->retrieveById($id))) {
            $this->setUser($user);

            return $user;
        }

        return false;
    }

    public function viaRemember()
    {
        return false;
    }

    public function logout(): void
    {
        $user = $this->user();

        $this->cache->forget($this->getName());

        $this->user = null;

        $this->loggedOut = true;
    }

    private function hasValidCredentials(?Authenticatable $user, array $credentials)
    {
        return !is_null($user) && $this->provider->validateCredentials($user, $credentials);
    }

    private function loginTimeInSeconds(): int
    {
        return (int) config('auth.guards.console.timeout');
    }

    private function getName(): string
    {
        return 'logged_in_user_id';
    }
}
