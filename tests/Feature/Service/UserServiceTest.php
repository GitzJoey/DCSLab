<?php

namespace Tests\Feature\Service;

use Exception;
use TypeError;
use App\Models\Role;
use App\Models\User;
use App\Models\Company;
use App\Models\Profile;
use App\Enums\UserRoles;
use Tests\ServiceTestCase;
use App\Enums\ActiveStatus;
use App\Enums\RecordStatus;
use App\Services\UserService;
use App\Actions\RandomGenerator;
use Illuminate\Support\Collection;
use Database\Seeders\UserTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = app(UserService::class);
        $this->randomGenerator = new RandomGenerator();
    }

    #region create
    public function test_user_service_call_create_expect_db_has_record()
    {
        $profileArr = Profile::factory()->setStatusActive()->make()->toArray();

        $userArr = User::factory()->make([
            'name' => strtolower($profileArr['first_name'].$profileArr['last_name']).$this->randomGenerator->generateNumber(1, 999)
        ])->toArray();
        $userArr['password'] = 'test123';

        $rolesArr = [];
        array_push($rolesArr, Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);

        $result = $this->userService->create(
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

    public function test_user_service_call_create_with_empty_array_parameters_expect_exception()
    {
        $userArr = [];
        $rolesArr = [];
        $profileArr = [];

        $this->expectException(Exception::class);
        $this->userService->create(
            $userArr,
            $rolesArr,
            $profileArr
        );
    }

    #endregion

    #region list

    public function test_user_service_call_list_with_paginate_true_expect_Paginator_object()
    {
        $user = User::factory()->create();

        $result = $this->userService->list(
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_user_service_call_list_with_paginate_false_expect_Collection_object()
    {
        $user = User::factory()->create();

        $result = $this->userService->list(
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_user_service_call_list_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = User::max('id') + 1;
        $result = $this->userService->list(
            search: '',
            paginate: false
        );

        $this->markTestSkipped('Masi bgng');

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_user_service_call_list_with_search_parameter_expect_filtered_results()
    {
        $uniqueIdentifier = $this->randomGenerator->generateAlphaNumeric(10);

        for ($iMode = 0; $iMode < 2; $iMode++) {
            for ($cInsert = 0; $cInsert < 10; $cInsert++) {
                $profileArr = Profile::factory()->setStatusActive()->make()->toArray();
                $wName = $this->randomGenerator->generateNumber(0, 1);

                if ($wName == 0) {
                    $profileArr['first_name'] = $iMode == 1 ? $profileArr['first_name'] . $uniqueIdentifier : $profileArr['first_name'];
                } elseif ($wName == 1) {
                    $profileArr['last_name'] = $iMode == 1 ? $profileArr['last_name'] . $uniqueIdentifier : $profileArr['last_name'];
                }
                
                $userArr = User::factory()->make([
                    'name' => strtolower($profileArr['first_name'].$profileArr['last_name']).$this->randomGenerator->generateNumber(1, 999)
                ])->toArray();
                $userArr['password'] = 'test123';
        
                $rolesArr = [];
                array_push($rolesArr, Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);
        
                $this->userService->create(
                    $userArr,
                    $rolesArr,
                    $profileArr
                );
            }
        }

        $result = $this->userService->list(
            search: $uniqueIdentifier,
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }

    public function test_user_service_call_list_with_page_parameter_negative_expect_results()
    {
        $uniqueIdentifier = $this->randomGenerator->generateAlphaNumeric(10);

        for ($cInsert = 0; $cInsert < 10; $cInsert++) {
            $profileArr = Profile::factory()->setStatusActive()->make()->toArray();
            $wName = $this->randomGenerator->generateNumber(0, 1);

            if ($wName == 0) {
                $profileArr['first_name'] = $profileArr['first_name'] . $uniqueIdentifier;
            } elseif ($wName == 1) {
                $profileArr['last_name'] = $profileArr['last_name'] . $uniqueIdentifier;
            }
            $userArr = User::factory()->make([
                'name' => strtolower($profileArr['first_name'].$profileArr['last_name']).$this->randomGenerator->generateNumber(1, 999)
            ])->toArray();
            $userArr['password'] = 'test123';
    
            $rolesArr = [];
            array_push($rolesArr, Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);
    
            $this->userService->create(
                $userArr,
                $rolesArr,
                $profileArr
            );
        }

        $result = $this->userService->list(
            search: $uniqueIdentifier,
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }

    public function test_user_service_call_list_with_perpage_parameter_negative_expect_results()
    {
        $uniqueIdentifier = $this->randomGenerator->generateAlphaNumeric(10);

        for ($cInsert = 0; $cInsert < 10; $cInsert++) {
            $profileArr = Profile::factory()->setStatusActive()->make()->toArray();
            $wName = $this->randomGenerator->generateNumber(0, 1);

            if ($wName == 0) {
                $profileArr['first_name'] = $profileArr['first_name'] . $uniqueIdentifier;
            } elseif ($wName == 1) {
                $profileArr['last_name'] = $profileArr['last_name'] . $uniqueIdentifier;
            }
            $userArr = User::factory()->make([
                'name' => strtolower($profileArr['first_name'].$profileArr['last_name']).$this->randomGenerator->generateNumber(1, 999)
            ])->toArray();
            $userArr['password'] = 'test123';
    
            $rolesArr = [];
            array_push($rolesArr, Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);
    
            $this->userService->create(
                $userArr,
                $rolesArr,
                $profileArr
            );
        }

        $result = $this->userService->list(
            search: $uniqueIdentifier,
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }

    #endregion

    #region read

    public function test_user_service_call_read_expect_object()
    {
        $profileArr = Profile::factory()->setStatusActive()->make()->toArray();

        $userArr = User::factory()->make([
            'name' => strtolower($profileArr['first_name'].$profileArr['last_name']).$this->randomGenerator->generateNumber(1, 999)
        ])->toArray();
        $userArr['password'] = 'test123';

        $rolesArr = [];
        array_push($rolesArr, Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);

        $result = $this->userService->create(
            $userArr,
            $rolesArr,
            $profileArr
        );

        $result = $this->userService->read($result);

        $this->assertInstanceOf(User::class, $result);
    }

    #endregion

    #region update

    public function test_user_service_call_update_expect_db_updated()
    {
        $profileArr = Profile::factory()->setStatusActive()->make()->toArray();

        $userArr = User::factory()->make([
            'name' => strtolower($profileArr['first_name'].$profileArr['last_name']).$this->randomGenerator->generateNumber(1, 999)
        ])->toArray();
        $userArr['password'] = 'test123';

        $rolesArr = [];
        array_push($rolesArr, Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);

        $user = $this->userService->create(
            $userArr,
            $rolesArr,
            $profileArr
        );

        $newProfileArr = Profile::factory()->setStatusActive()->make()->toArray();

        $newUserArr = User::factory()->make([
            'name' => strtolower($newProfileArr['first_name'].$newProfileArr['last_name']).$this->randomGenerator->generateNumber(1, 999)
        ])->toArray();
        $newUserArr['password'] = 'test123';

        $newRolesArr = [];
        array_push($newRolesArr, Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);
    
        $result = $this->userService->update(
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

    public function test_user_service_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $profileArr = Profile::factory()->setStatusActive()->make()->toArray();

        $userArr = User::factory()->make([
            'name' => strtolower($profileArr['first_name'].$profileArr['last_name']).$this->randomGenerator->generateNumber(1, 999)
        ])->toArray();
        $userArr['password'] = 'test123';

        $rolesArr = [];
        array_push($rolesArr, Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);

        $user = $this->userService->create(
            $userArr,
            $rolesArr,
            $profileArr
        );

        $newUserArr = [];
        $newRolesArr = [];
        $newProfileArr = [];

        $this->userService->update(
            $user,
            $newUserArr,
            $newRolesArr,
            $newProfileArr
        );
    }

    #endregion

    #region delete

    public function test_user_service_call_delete_expect_bool()
    {
        $profileArr = Profile::factory()->setStatusActive()->make()->toArray();

        $userArr = User::factory()->make([
            'name' => strtolower($profileArr['first_name'].$profileArr['last_name']).$this->randomGenerator->generateNumber(1, 999)
        ])->toArray();
        $userArr['password'] = 'test123';

        $rolesArr = [];
        array_push($rolesArr, Role::where('name', '=', UserRoles::DEVELOPER->value)->first()->id);

        $user = $this->userService->create(
            $userArr,
            $rolesArr,
            $profileArr
        );

        $user = User::find($user->id);
        $user->profile->status = RecordStatus::INACTIVE->value;

        // $result = $user->delete();

        // $this->assertIsBool($result);
        // $this->assertTrue($result);
        // $this->assertSoftDeleted('profiles', [
        //     'id' => $user->id,
        // ]);

        $this->markTestSkipped('Masi bgng');
    }

    #endregion

    #region others

    #endregion
}
