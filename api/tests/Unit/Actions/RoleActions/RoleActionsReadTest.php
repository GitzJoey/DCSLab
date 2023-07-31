<?php

namespace Tests\Unit\Actions\RoleActions;

use App\Actions\Role\RoleActions;
use Illuminate\Database\Eloquent\Collection;
use Tests\ActionsTestCase;

class RoleActionsReadTest extends ActionsTestCase
{
    private RoleActions $roleActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleActions = new RoleActions();
    }

    public function test_role_actions_call_get_all_roles_expect_collections()
    {
        $result = $this->roleActions->getAllRoles();

        $this->assertInstanceOf(Collection::class, $result);
    }
}
