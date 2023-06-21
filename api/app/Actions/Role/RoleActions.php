<?php

namespace App\Actions\Role;

use App\Enums\UserRoles;
use App\Models\Permission;
use App\Models\Role;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function create(array $roleArr, array $inputtedPermissionsArr): Role
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $role = new Role();
            $role->name = $roleArr['name'];
            $role->display_name = $roleArr['display_name'];
            $role->description = $roleArr['description'];

            $role->save();

            foreach ($inputtedPermissionsArr as $pl) {
                $role->permissions()->attach($pl);
            }

            DB::commit();

            return $role;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.' '.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function readAny(array $relationship = [], array $exclude = []): Collection
    {
        $role = Role::with($relationship)->latest();

        if (empty($exclude)) {
            return $role->get();
        } else {
            return $role->whereNotIn('name', [UserRoles::DEVELOPER->value, UserRoles::ADMINISTRATOR->value])->get();
        }
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

    public function update(
        Role $role,
        array $roleArr,
        array $inputtedPermissions
    ): Role {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $pl = Permission::whereIn('id', $inputtedPermissions)->get();

            $role->syncPermissions($pl);

            $role->update([
                'name' => $roleArr['name'],
                'display_name' => $roleArr['display_name'],
                'description' => $roleArr['description'],
            ]);

            DB::commit();

            return $role;
        } catch (Exception $e) {
            DB::rollBack();
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.' '.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function getAllPermissions(): Collection
    {
        return Permission::get();
    }
}
