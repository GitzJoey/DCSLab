<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface UserService
{
    public function register(array $input): User;

    public function create(
        array $user,
        array $roles,
        array $profile
    ): User;

    public function read(string $search = '', bool $paginate = true, int $page = 1, int $perPage = 10, bool $useCache = true): Paginator|Collection;

    public function readBy(string $key, string $value);

    public function update(
        User $user,
        ?array $userArr = null,
        ?array $roles = null,
        ?array $profile = null,
        ?array $settings = null
    ): User;

    public function updateUser(User $user, array $userArr, bool $useTransactions = true): bool;

    public function updateProfile(User $user, array $profile, bool $useTransactions = true): bool;

    public function updateRoles(User $user, array $rolesId, bool $useTransactions = true): User;

    public function updateSettings(User $user, array $settings, bool $useTransactions = true): bool;

    public function resetPassword(string $email): void;

    public function resetTokens(User $user): void;

    public function createDefaultSetting(): array;
}
