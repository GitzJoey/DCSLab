<?php

namespace App\Services\Impls;

use App\Enums\UserRoles;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Role;
use App\Models\Permission;

use App\Services\RoleService;
use App\Traits\CacheHelper;

class RoleServiceImpl implements RoleService
{
    use CacheHelper;

    public function __construct()
    {
        
    }
    
    public function create(array $role, array $permissions): Role
    {
        DB::beginTransaction();
        $timer_start = microtime(true);

        try {
            $role = new Role();
            $role->name = $role['name'];
            $role->display_name = $role['display_name'];
            $role->description = $role['description'];

            $role->save();

            foreach ($permissions as $pl) {
                $role->permissions()->attach($pl);
            }

            DB::commit();

            return $role;
        } catch (Exception $e) {
            DB::rollBack();
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
        } finally {
            $execution_time = microtime(true) - $timer_start;
            Log::channel('perfs')->info('['.session()->getId().'-'.' '.'] '.__METHOD__.' ('.number_format($execution_time, 1).'s)');
        }
    }

    public function read(array $relationship = [], array $exclude = []): Collection
    {
        $role = Role::with($relationship)->latest();

        if (empty($exclude)) {
            return $role->get();
        } else {
            return $role->whereNotIn('name', [UserRoles::DEVELOPER->value, UserRoles::ADMINISTRATOR->value])->get();
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
        Role $role,
        array $roleArr,
        array $inputtedPermissions
    ): Role
    {
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
            Log::debug('['.session()->getId().'-'.(is_null(auth()->user()) ? '':auth()->id()).'] '.__METHOD__.$e);
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
