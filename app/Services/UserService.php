<?php

namespace App\Services;

use App\Models\User;

interface UserService
{
    public function register(
        string $name,
        string $email,
        string $password,
        string $terms
    ): User;

    public function create(
        string $name,
        string $email,
        string $password,
        array $rolesId,
        array $profile
    ): User ;

    public function read(string $search = '', bool $paginate = true, int $perPage = 10);

    public function readBy(string $key, string $value);

    public function update(
        int $id,
        string $name,
        array $rolesId,
        array $profile,
        array $settings
    ): User;

    public function updateUser(User $user, string $name, bool $useTransactions = true);

    public function updateProfile(User $user, array $profile, bool $useTransactions = true);

    public function updateRoles(User $user, array $rolesId, bool $useTransactions = true);

    public function updateSettings(User $user, array $settings, bool $useTransactions = true);

    public function resetPassword(string $email): void;

    public function resetTokens(int $id): void;

    public function createDefaultSetting(): array;
}
