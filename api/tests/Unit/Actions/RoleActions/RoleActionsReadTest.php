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

    public function test_role_actions_call_read_any_with_default_parameter_expect_collection_object()
    {
        $result = $this->roleActions->readAny();

        $this->assertInstanceOf(Collection::class, $result);
    }
}
