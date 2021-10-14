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

    public function read($parameters = null);

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
