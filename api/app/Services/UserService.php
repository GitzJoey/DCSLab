<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;

interface UserService
{
    public function register(array $input): User;

    public function create(
        array $userArr,
        array $rolesArr,
        array $profileArr
    ): User;

    public function list(
        string $search = '',
        bool $paginate = true,
        int $page = 1,
        int $perPage = 10,
        bool $useCache = true
    ): Paginator|Collection;

    public function read(User $user): User;

    public function readBy(string $key, string $value);

    public function update(
        User $user,
        ?array $userArr = null,
        ?array $rolesArr = null,
        ?array $profileArr = null,
        ?array $settingsArr = null
    ): User;

    public function updateUser(User $user, array $userArr, bool $useTransactions = true): bool;

    public function updateProfile(User $user, array $profile, bool $useTransactions = true): bool;

    public function updateRoles(User $user, array $rolesId, bool $useTransactions = true): User;

    public function updateSettings(User $user, array $settings, bool $useTransactions = true): bool;

    public function changePassword(User $user, string $newPassword): void;

    public function resetTokens(User $user): void;

    public function createDefaultSetting(): array;
}
