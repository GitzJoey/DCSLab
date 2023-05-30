<?php

namespace Tests\Unit\Actions\UserActions;

use App\Actions\User\UserActions;
use App\Enums\UserRoles;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Tests\ActionsTestCase;

class UserActionsReadTest extends ActionsTestCase
{
    private UserActions $userActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userActions = new UserActions();
    }

    public function test_user_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        User::factory()->create();

        $result = $this->userActions->readAny(
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_user_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        User::factory()->create();

        $result = $this->userActions->readAny(
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_user_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $uniqueIdentifier = uniqid();

        for ($iMode = 0; $iMode < 2; $iMode++) {
            for ($cInsert = 0; $cInsert < 10; $cInsert++) {
                $profileArr = Profile::factory()->setStatusActive()->make()->toArray();
                $wName = random_int(0, 1);

                if ($wName == 0) {
                    $profileArr['first_name'] = $iMode == 1 ? $profileArr['first_name'].$uniqueIdentifier : $profileArr['first_name'];
                } elseif ($wName == 1) {
                    $profileArr['last_name'] = $iMode == 1 ? $profileArr['last_name'].$uniqueIdentifier : $profileArr['last_name'];
                }

                $userArr = User::factory()->make([
                    'name' => strtolower($profileArr['first_name'].$profileArr['last_name']).random_int(1, 999),
                ])->toArray();
                $userArr['password'] = 'test123';

                $rolesArr = [];
                array_push($rolesArr, Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);

                $this->userActions->create(
                    $userArr,
                    $rolesArr,
                    $profileArr
                );
            }
        }

        $result = $this->userActions->readAny(
            search: $uniqueIdentifier,
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }

    public function test_user_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $uniqueIdentifier = uniqid();

        for ($cInsert = 0; $cInsert < 10; $cInsert++) {
            $profileArr = Profile::factory()->setStatusActive()->make()->toArray();
            $wName = random_int(0, 1);

            if ($wName == 0) {
                $profileArr['first_name'] = $profileArr['first_name'].$uniqueIdentifier;
            } elseif ($wName == 1) {
                $profileArr['last_name'] = $profileArr['last_name'].$uniqueIdentifier;
            }
            $userArr = User::factory()->make([
                'name' => strtolower($profileArr['first_name'].$profileArr['last_name']).random_int(1, 999),
            ])->toArray();
            $userArr['password'] = 'test123';

            $rolesArr = [];
            array_push($rolesArr, Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);

            $this->userActions->create(
                $userArr,
                $rolesArr,
                $profileArr
            );
        }

        $result = $this->userActions->readAny(
            search: $uniqueIdentifier,
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }

    public function test_user_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $uniqueIdentifier = uniqid();

        for ($cInsert = 0; $cInsert < 10; $cInsert++) {
            $profileArr = Profile::factory()->setStatusActive()->make()->toArray();
            $wName = random_int(0, 1);

            if ($wName == 0) {
                $profileArr['first_name'] = $profileArr['first_name'].$uniqueIdentifier;
            } elseif ($wName == 1) {
                $profileArr['last_name'] = $profileArr['last_name'].$uniqueIdentifier;
            }
            $userArr = User::factory()->make([
                'name' => strtolower($profileArr['first_name'].$profileArr['last_name']).random_int(1, 999),
            ])->toArray();
            $userArr['password'] = 'test123';

            $rolesArr = [];
            array_push($rolesArr, Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);

            $this->userActions->create(
                $userArr,
                $rolesArr,
                $profileArr
            );
        }

        $result = $this->userActions->readAny(
            search: $uniqueIdentifier,
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }

    public function test_user_actions_call_read_expect_object()
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

        $result = $this->userActions->read($result);

        $this->assertInstanceOf(User::class, $result);
    }
}
