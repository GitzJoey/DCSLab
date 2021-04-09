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
        $settings
    );

    public function read();

    public function update(
        $id,
        $name,
        $email,
        $password,
        $rolesId,
        $profile,
        $settings
    );

    public function delete($id);
}
