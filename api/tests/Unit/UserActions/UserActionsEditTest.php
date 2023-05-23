<?php

namespace Tests\Unit\UserActions;

use App\Actions\User\UserActions;
use App\Enums\UserRoles;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class UserActionsEditTest extends ActionsTestCase
{
    private UserActions $userActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userActions = new UserActions();
    }

    public function test_user_actions_call_update_expect_db_updated()
    {
        $profileArr = Profile::factory()->setStatusActive()->make()->toArray();

        $userArr = User::factory()->make([
            'name' => strtolower($profileArr['first_name'].$profileArr['last_name']).random_int(1, 999),
        ])->toArray();
        $userArr['password'] = 'test123';

        $rolesArr = [];
        array_push($rolesArr, Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);

        $user = $this->userActions->create(
            $userArr,
            $rolesArr,
            $profileArr
        );

        $newProfileArr = Profile::factory()->setStatusActive()->make()->toArray();

        $newUserArr = User::factory()->make([
            'name' => strtolower($newProfileArr['first_name'].$newProfileArr['last_name']).random_int(1, 999),
        ])->toArray();
        $newUserArr['password'] = 'test123';

        $newRolesArr = [];
        array_push($newRolesArr, Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);

        $result = $this->userActions->update(
            $user,
            $newUserArr,
            $newRolesArr,
            $newProfileArr
        );

        $this->assertDatabaseHas('users', [
            'id' => $result->id,
            'email' => $userArr['email'],
            'name' => $newUserArr['name'],
        ]);

        $this->assertDatabaseHas('profiles', [
            'address' => $newProfileArr['address'],
            'city' => $newProfileArr['city'],
            'postal_code' => $newProfileArr['postal_code'],
            'country' => $newProfileArr['country'],
            'tax_id' => $newProfileArr['tax_id'],
            'ic_num' => $newProfileArr['ic_num'],
            'status' => $newProfileArr['status'],
            'remarks' => $newProfileArr['remarks'],
        ]);
    }

    public function test_user_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $profileArr = Profile::factory()->setStatusActive()->make()->toArray();

        $userArr = User::factory()->make([
            'name' => strtolower($profileArr['first_name'].$profileArr['last_name']).random_int(1, 999),
        ])->toArray();
        $userArr['password'] = 'test123';

        $rolesArr = [];
        array_push($rolesArr, Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);

        $user = $this->userActions->create(
            $userArr,
            $rolesArr,
            $profileArr
        );

        $newUserArr = [];
        $newRolesArr = [];
        $newProfileArr = [];

        $this->userActions->update(
            $user,
            $newUserArr,
            $newRolesArr,
            $newProfileArr
        );
    }
}
