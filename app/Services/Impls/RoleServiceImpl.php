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
        $name,
        $display_name,
        $description,
        $permissions
    )
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

            return $role->hId();
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read()
    {
        return Role::with('permissions')->paginate(Config::get('const.DEFAULT.PAGINATION_LIMIT'));
    }

    public function readRoles($withDefaultRole)
    {
        if ($withDefaultRole)
        {
            return Role::all()->pluck('display_name', 'hId');
        } else {
            return Role::whereNotIn('name', ['dev','administrator'])->get()->pluck('display_name', 'hId');
        }
    }

    public function update(
        $id,
        $name,
        $display_name,
        $description,
        $inputtedPermissions
    )
    {
        DB::beginTransaction();

        try {
            $role = Role::with('permissions')->where('id', '=', $id)->first();
            $pl = Permission::whereIn('id', $inputtedPermissions)->get();

            $role->syncPermissions($pl);

            $retval = $role->update([
                'name' => $name,
                'display_name' => $display_name,
                'description' => $description,
            ]);

            DB::commit();

            return $retval;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function getAllPermissions()
    {
        return Permission::get();
    }

    public function getRoleById($id)
    {
        return Role::find($id);
    }

    public function getRoleByName($name)
    {
        return Role::where('name', $name)->first();
    }

    public function getRoleByDisplayName($name, $caseSensitive)
    {
        if ($caseSensitive) {
            return Role::where('display_name', $name)->first();
        } else {
            return Role::whereRaw("UPPER(display_name) = '".strtoupper($name)."'")->first();
        }
    }
}
