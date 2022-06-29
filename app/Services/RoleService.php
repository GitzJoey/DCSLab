<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Collection;

interface RoleService
{
    public function create(array $roleArr, array $inputtedPermissionsArr): Role;

    public function list(array $relationship = [], array $exclude = []): Collection;

    public function read(Role $role): Role;

    public function readBy(string $key, string $value);

    public function update(
        Role $role,
        array $roleArr,
        array $inputtedPermissionsArr
    ): Role;

    public function getAllPermissions(): Collection;
}
