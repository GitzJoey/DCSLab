<?php

namespace App\Services;

interface UserService
{
    public function register(
        $name,
        $email,
        $password,
        $terms
    );

    public function create(
        $name,
        $email,
        $password,
        $rolesId,
        $profile
    );

    public function read($search = '', $paginate = true, $perPage = 10);

    public function readBy($key, $value);

    public function update(
        $id,
        $name,
        $rolesId,
        $profile,
        $settings
    );

    public function resetPassword($email);

    public function resetTokens($id);

    public function createDefaultSetting();
}
