<?php

namespace App\Services;

interface RoleService
{
    public function create(
        $name,
        $display_name,
        $description,
        $permissions
    );

    public function read();

    public function readRoles($withDefaultRole);

    public function update(
        $id,
        $name,
        $display_name,
        $description,
        $inputtedPermissions
    );

    public function getAllPermissions();

    public function getRoleById($id);

    public function getRoleByName($name);

    public function getRoleByDisplayName($name, $caseSensitive);
}
