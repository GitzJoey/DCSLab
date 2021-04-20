<?php

namespace App\Services;

interface UserService
{
    public function create(
        $name,
        $email,
        $password,
        $rolesId,
        $profile,
        $setting
    );

    public function read();

    public function readCreatedById($id);

    public function getMyProfile($id);

    public function update(
        $id,
        $name,
        $email,
        $password,
        $rolesId,
        $profile,
        $setting
    );

    public function ban($id, $reason);

    public function createDefaultSetting();
}
