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

    public function update(
        $id,
        $name,
        $display_name,
        $description,
        $permissions,
        $inputtedPermissions
    );
    public function delete($id);

    public function getAllPermissions();
}
