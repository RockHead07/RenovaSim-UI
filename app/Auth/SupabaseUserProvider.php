<?php

namespace App\Auth;

use App\Models\User;
use App\Services\SupabaseService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class SupabaseUserProvider implements UserProvider
{
    public function __construct(protected SupabaseService $supabase) {}

    public function retrieveById($identifier): ?Authenticatable
    {
        $users = $this->supabase->select('users', '*', ['id' => $identifier]);
        return !empty($users) ? $this->hydrate($users[0]) : null;
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        return null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void {}

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        $login = $credentials['email'] ?? '';
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $users = $this->supabase->select('users', '*', [$field => $login]);
        return !empty($users) ? $this->hydrate($users[0]) : null;
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        return Hash::check($credentials['password'], $user->getAuthPassword());
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void {}

    protected function hydrate(array $data): User
    {
        $user = new User();
        $user->setRawAttributes($data);
        $user->exists = true;
        return $user;
    }
}
