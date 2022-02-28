<?php

namespace App\Services\Impls;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Role;
use App\Models\Permission;

use App\Services\RoleService;

class RoleServiceImpl implements RoleService
{
    public function create(
        string $name,
        string $display_name,
        string $description,
        array $permissions
    ): Role
    {
        DB::beginTransaction();

        try {
            $role = new Role();
            $role->name = $name;
            $role->display_name = $display_name;
            $role->description = $description;

            $role->save();

            foreach ($permissions as $pl) {
                $role->permissions()->attach($pl);
            }

            DB::commit();

            return $role->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        }
    }

    public function read(array $relationship = [], array $exclude = [])
    {
        $role = Role::with($relationship)->latest();

        if (empty($exclude)) {
            return $role->get();
        } else {
            return $role->whereNotIn('name', ['dev','administrator'])->get();
        }
    }

    public function readBy(string $key, string $value)
    {
        switch(strtoupper($key)) {
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

    public function update(
        int $id,
        string $name,
        string $display_name,
        string $description,
        array $inputtedPermissions
    ): Role
    {
        DB::beginTransaction();

        try {
            $role = Role::with('permissions')->where('id', '=', $id)->first();
            $pl = Permission::whereIn('id', $inputtedPermissions)->get();

            $role->syncPermissions($pl);

            $role->update([
                'name' => $name,
                'display_name' => $display_name,
                'description' => $description,
            ]);

            DB::commit();

            return $role;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.DEFAULT.ERROR_RETURN_VALUE');
        }
    }

    public function getAllPermissions()
    {
        return Permission::get();
    }
}
