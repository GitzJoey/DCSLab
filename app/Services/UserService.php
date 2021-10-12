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

    public function updateProfile(
        $id,
        $profile
    );

    public function resetPassword($email);

    public function resetTokens($id);

    public function createDefaultSetting();

    public function getUserById($id);

    public function getUserByEmail($email);

    public function getAllUserExceptMe($email);
}
