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

            return $role->hId;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug($e);
            return Config::get('const.ERROR_RETURN_VALUE');
        }
    }

    public function read($parameters = null)
    {
        if ($parameters == null) {
            return Role::with('permissions')->get();
        }

        if (array_key_exists('readById', $parameters))  {
            return Role::find($parameters['readById']);
        }

        if (array_key_exists('readByName', $parameters))  {
            return Role::where('name', $parameters['readByName'])->first();
        }

        if (array_key_exists('readByDisplayName', $parameters))  {
            return Role::whereRaw("UPPER(display_name) = '".strtoupper($parameters['readByDisplayName'])."'")->first();
        }

        if (array_key_exists('readByDisplayNameCaseSensitive', $parameters)) {
            return Role::where('display_name', $parameters['readByDisplayNameCaseSensitive'])->first();
        }

        if (array_key_exists('withDefaultRole', $parameters)) {
            if (!$parameters['withDefaultRole'])
                return Role::whereNotIn('name', ['dev','administrator'])->get()->pluck('display_name', 'hId');
            else
                return Role::get()->pluck('display_name', 'hId');
        }

        return null;
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
}
