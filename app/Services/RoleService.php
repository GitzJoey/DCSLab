<?php

namespace App\Services;

use App\Models\Role;

interface RoleService
{
    public function create(
        string $name,
        string $display_name,
        string $description,
        array $permissions
    ): Role;

    public function read(array $parameters = []);

    public function readBy(string $key, string $value);

    public function update(
        int $id,
        string $name,
        string $display_name,
        string $description,
        array $inputtedPermissions
    ): Role;

    public function getAllPermissions();
}
