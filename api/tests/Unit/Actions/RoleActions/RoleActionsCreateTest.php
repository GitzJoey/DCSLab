<?php

namespace Tests\Unit\Actions\RoleActions;

use App\Actions\Role\RoleActions;
use App\Models\Role;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class RoleActionsCreateTest extends ActionsTestCase
{
    private RoleActions $roleActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleActions = new RoleActions();
    }

    public function test_role_actions_call_create_expect_db_has_record()
    {
        User::factory()
            ->has(Role::factory())
            ->create();

        $roleArr = Role::factory()->make()->toArray();

        $result = $this->roleActions->create($roleArr);

        $this->assertDatabaseHas('rolees', [
            'id' => $result->id,
            'name' => $roleArr['name'],
            'display_name' => $roleArr['display_name'],
            'description' => $roleArr['description'],
        ]);
    }

    public function test_role_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->roleActions->create([]);
    }
}
