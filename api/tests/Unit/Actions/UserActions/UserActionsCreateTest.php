<?php

namespace Tests\Unit\Actions\UserActions;

use App\Actions\User\UserActions;
use App\Enums\UserRoles;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class UserActionsCreateTest extends ActionsTestCase
{
    private UserActions $userActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userActions = new UserActions();
    }

    public function test_user_actions_call_create_expect_db_has_record()
    {
        $profileArr = Profile::factory()->setStatusActive()->make()->toArray();

        $userArr = User::factory()->make([
            'name' => strtolower($profileArr['first_name'].$profileArr['last_name']).random_int(1, 999),
        ])->toArray();
        $userArr['password'] = 'test123';

        $rolesArr = [];
        array_push($rolesArr, Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);

        $result = $this->userActions->create(
            $userArr,
            $rolesArr,
            $profileArr
        );

        $this->assertDatabaseHas('users', [
            'id' => $result->id,
            'name' => $userArr['name'],
            'email' => $userArr['email'],
        ]);

        $this->assertDatabaseHas('profiles', [
            'address' => $profileArr['address'],
            'city' => $profileArr['city'],
            'postal_code' => $profileArr['postal_code'],
            'country' => $profileArr['country'],
            'tax_id' => $profileArr['tax_id'],
            'ic_num' => $profileArr['ic_num'],
            'status' => $profileArr['status'],
            'remarks' => $profileArr['remarks'],
        ]);
    }

    public function test_user_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $userArr = [];
        $rolesArr = [];
        $profileArr = [];

        $this->expectException(Exception::class);
        $this->userActions->create(
            $userArr,
            $rolesArr,
            $profileArr
        );
    }
}
