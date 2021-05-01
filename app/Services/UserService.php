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

    public function read();

    public function readCreatedById($id);

    public function getMyProfile($id);

    public function update(
        $id,
        $name,
        $rolesId,
        $profile,
        $settings
    );

    public function resetPassword($id);

    public function resetToken($id, $tokenType);

    public function createDefaultSetting();
}
