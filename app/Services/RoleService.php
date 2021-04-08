<?php

namespace App\Services;

interface RoleService
{
    public function create(
        $name,
        $display_name,
        $description,
        $permission
    );
    public function read();
    public function update(
        $id,
        $name,
        $display_name,
        $description,
        $permission,
        $inputtedPermission
    );
    public function delete($id);

    public function getAllPermissions();
}
