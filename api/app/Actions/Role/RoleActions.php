<?php

namespace App\Actions\Role;

use App\Enums\UserRoles;
use App\Models\Permission;
use App\Models\Role;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Illuminate\Support\Collection;

class RoleActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function readAny(array $relationship = [], array $onlyName = [], bool $excludeDevAdminRole = false): Collection
    {
        $role = Role::with($relationship)->latest();

        if (count($onlyName) > 0) {
            $role = $role->whereIn('name', $onlyName);
        }

        if ($excludeDevAdminRole) {
            $role = $role->whereNotIn('name', [UserRoles::DEVELOPER->value, UserRoles::ADMINISTRATOR->value]);
        }

        return $role->get();
    }

    public function read(Role $role): Role
    {
        return $role->with('permissions')->first();
    }

    public function readBy(string $key, string $value): Role|null
    {
        switch (strtoupper($key)) {
            case 'ID':
                return Role::find($value);
            case 'NAME':
                return Role::where('name', $value)->first();
            case 'DISPLAY_NAME':
                return Role::whereRaw("UPPER(display_name) = '".strtoupper($value)."'")->first();
            case 'DISPLAY_NAME_CASE_SENSITIVE':
                return Role::where('display_name', $value)->first();
            default:
                return null;
        }
    }

    public function getAllPermissions(string $roleName = ''): Collection
    {
        if (empty($role)) {
            return Permission::get();
        }

        $role = Role::where('name', $roleName)->first();

        if ($role) {
            return $role->permissions()->get();
        }
    }

    public function getAllPermissions(): Collection
    {
        return Permission::get();
    }

    public function getAllRoles(): Collection
    {
        return Role::get();
    }
}
