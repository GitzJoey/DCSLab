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
        $permission
    )
    {
        DB::beginTransaction();

        try {
            $role = new Role();
            $role->name = $name;
            $role->display_name = $display_name;
            $role->description = $description;
            $role->save();

            foreach ($permission as $pl) {
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

    public function update(
        $id,
        $name,
        $display_name,
        $description,
        $permission,
        $inputtedPermission
    )
    {
        DB::beginTransaction();

        try {
            $role = Role::with('permissions')->where('id', '=', $id)->first();
            $pl = Permission::whereIn('id', $permission)->get();

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

    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $role = Role::with('permissions')->find($id);

            $role->detachPermissions($role->getSelectedPermissionIdsAttribute());

            $retval = $role->delete();

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
}
