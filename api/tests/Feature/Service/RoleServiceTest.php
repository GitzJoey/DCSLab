<?php

namespace Tests\Feature\Service;

use Exception;
use TypeError;
use App\Models\Role;
use App\Models\User;
use App\Models\Company;
use App\Models\Permission;
use Tests\ServiceTestCase;
use App\Services\RoleService;
use App\Actions\RandomGenerator;
use Illuminate\Support\Collection;
use Database\Seeders\RoleTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleServiceTest extends ServiceTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->roleService = app(RoleService::class);
        $this->randomGenerator = new RandomGenerator();
    }

    #region create
    public function test_role_service_call_create_expect_db_has_record()
    {
        $roleArr = Role::factory()->make()->toArray();

        $permissionCount = Permission::count();
        $supplierProductsCount = $this->randomGenerator->generateNumber(1, $permissionCount); 
        $inputtedPermissionsArr = Permission::inRandomOrder()->take($supplierProductsCount)->pluck('id')->toArray();

        $result = $this->roleService->create(
            $roleArr,
            $inputtedPermissionsArr
        );

        $this->assertDatabaseHas('roles', [
            'id' => $result->id,
            'name' => $roleArr['name'],
            'display_name' => $roleArr['display_name'],
            'description' => $roleArr['description'],
        ]);

        for ($i = 0; $i < count($inputtedPermissionsArr) ; $i++) {
            $this->assertDatabaseHas('permission_role', [
                'permission_id' => $inputtedPermissionsArr[$i],
                'role_id' => $result->id,
            ]);
        }
    }

    public function test_role_service_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->roleService->create(
            [],
            [],
        );
    }

    #endregion

    #region list
    public function test_role_service_call_list_with_paginate_false_expect_collection_object()
    {
        $result = $this->roleService->list(
            relationship: [],
            exclude: []
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    #endregion

    #region read

    public function test_role_service_call_read_expect_object()
    {
        $roleSeeder = new RoleTableSeeder();
        $roleSeeder->callWith(RoleTableSeeder::class, [true, 5]);

        $role = Role::inRandomOrder()->first();

        $result = $this->roleService->read($role);

        $this->assertInstanceOf(Role::class, $result);
    }

    #endregion

    #region update

    public function test_role_service_call_update_expect_db_updated()
    {
        $roleSeeder = new RoleTableSeeder();
        $roleSeeder->callWith(RoleTableSeeder::class, [true, 5]);

        $role = Role::inRandomOrder()->first();

        $roleArr = Role::factory()->make()->toArray();

        $permissionCount = Permission::count();
        $supplierProductsCount = $this->randomGenerator->generateNumber(1, $permissionCount); 
        $inputtedPermissionsArr = Permission::inRandomOrder()->take($supplierProductsCount)->pluck('id')->toArray();

        $result = $this->roleService->update($role, $roleArr, $inputtedPermissionsArr);
        
        $this->assertInstanceOf(Role::class, $result);

        $this->assertDatabaseHas('roles', [
            'id' => $result->id,
            'name' => $roleArr['name'],
            'display_name' => $roleArr['display_name'],
            'description' => $roleArr['description'],
        ]);

        for ($i = 0; $i < count($inputtedPermissionsArr) ; $i++) {
            $this->assertDatabaseHas('permission_role', [
                'permission_id' => $inputtedPermissionsArr[$i],
                'role_id' => $result->id,
            ]);
        }
    }

    public function test_role_service_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $roleSeeder = new RoleTableSeeder();
        $roleSeeder->callWith(RoleTableSeeder::class, [true, 5]);

        $role = Role::inRandomOrder()->first();

        $this->roleService->update(
            $role,
            [],
            []
        );
    }

    #endregion

    #region delete

    #endregion

    #region others

    #endregion
}
