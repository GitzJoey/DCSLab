<?php

namespace Tests\Feature\API;

use Exception;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Branch;
use Tests\APITestCase;
use App\Models\Company;
use App\Models\Profile;
use App\Enums\UserRoles;
use App\Models\Employee;
use App\Enums\ActiveStatus;
use App\Services\RoleService;
use App\Services\UserService;
use App\Actions\RandomGenerator;
use App\Services\EmployeeService;
use Illuminate\Container\Container;
use Vinkla\Hashids\Facades\Hashids;
use Database\Seeders\BranchTableSeeder;
use Database\Seeders\EmployeeTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Factories\Sequence;

class EmployeeAPITest extends APITestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->randomGenerator = new RandomGenerator();
    }

    /* #region store */
    public function test_employee_api_call_store_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                        ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $accessCount = $this->randomGenerator->generateNumber(1, $company->branches()->count());
        $branches = $company->branches()->inRandomOrder()->take($accessCount)->get();
        $accessBranchIds = [];
        for ($i = 0; $i < $accessCount; $i++) {
            array_push($accessBranchIds, Hashids::encode($branches[$i]->id));
        }

        $userArr = User::factory()->make()->toArray();

        $profileArr = Profile::factory()->make()->toArray();

        $employeeArr = array_merge([
            'company_id' => Hashids::encode($companyId),
            'name' => $userArr['name'],
            'email' => $userArr['email'],
            'address' => $profileArr['address'],
            'city' => $profileArr['city'],
            'postal_code' => $profileArr['postal_code'],
            'country' => $profileArr['country'],
            'tax_id' => $profileArr['tax_id'],
            'ic_num' => $profileArr['ic_num'],
            'remarks' => $profileArr['remarks'],
            'accessBranchIds' => $accessBranchIds,
        ], Employee::factory()->setStatusActive()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.company.employee.save'), $employeeArr);

        $api->assertSuccessful();

        $employeeId = $company->employees->where('code', '=', $employeeArr['code'])->first()->id;

        $this->assertDatabaseHas('employees', [
            'id' => $employeeId,
            'company_id' => $companyId,
            'code' => $employeeArr['code']
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $employeeArr['name'],
            'email' => $employeeArr['email']
        ]);

        $this->assertDatabaseHas('profiles', [
            'address' => $employeeArr['address'],
            'city' => $employeeArr['city'],
            'postal_code' => $employeeArr['postal_code'],
            'country' => $employeeArr['country'],
            'tax_id' => $employeeArr['tax_id'],
            'ic_num' => $employeeArr['ic_num'],
            'status' => $employeeArr['status'],
            'remarks' => $employeeArr['remarks']
        ]);

        for ($i = 0; $i < $accessCount ; $i++) {           
            $this->assertDatabaseHas('employee_accesses', [
                'employee_id' => $employeeId,
                'branch_id' => $branches[$i]->id
            ]);
        }
    }

    public function test_employee_api_call_store_with_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                        ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $this->actingAs($user);
        
        $company = $user->companies->first();
        $companyId = $company->id;

        $employeeSeeder = new EmployeeTableSeeder();
        $employeeSeeder->callWith(EmployeeTableSeeder::class, [3, $companyId]);

        $accessCount = $this->randomGenerator->generateNumber(1, $company->branches()->count());
        $branches = $company->branches()->inRandomOrder()->take($accessCount)->get();
        $accessBranchIds = [];
        for ($i = 0; $i < $accessCount; $i++) {
            array_push($accessBranchIds, Hashids::encode($branches[$i]->id));
        }

        $userArr = User::factory()->make()->toArray();

        $profileArr = Profile::factory()->make()->toArray();

        $employeeArr = Employee::factory()->setStatusActive()->make()->toArray();
        $employeeArr = array_merge($employeeArr, [
            'company_id' => Hashids::encode($companyId),
            'code' => $company->employees()->inRandomOrder()->first()->code,
            'name' => $userArr['name'],
            'email' => $userArr['email'],
            'address' => $profileArr['address'],
            'city' => $profileArr['city'],
            'postal_code' => $profileArr['postal_code'],
            'country' => $profileArr['country'],
            'tax_id' => $profileArr['tax_id'],
            'ic_num' => $profileArr['ic_num'],
            'remarks' => $profileArr['remarks'],
            'accessBranchIds' => $accessBranchIds,
        ]);

        $api = $this->json('POST', route('api.post.db.company.employee.save'), $employeeArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_employee_api_call_store_with_existing_code_in_different_company_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->count(2)->state(new Sequence(['default' => true], ['default' => false])), 'companies')
                    ->create();

        $company = $user->companies->first();

        $this->actingAs($user);

        $employeeSeeder = new EmployeeTableSeeder();

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;
        $employeeSeeder->callWith(EmployeeTableSeeder::class, [3, $companyId_1]);

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;
        $employeeSeeder->callWith(EmployeeTableSeeder::class, [3, $companyId_2]);

        $userArr = User::factory()->make()->toArray();
        $profileArr = Profile::factory()->make()->toArray();
        $employee_company_2 = $company_2->employees()->inRandomOrder()->first();
        $employeeArr = array_merge([
            'company_id' => Hashids::encode($companyId_2),
            'name' => $userArr['name'],
            'email' => $userArr['email'],
            'address' => $profileArr['address'],
            'city' => $profileArr['city'],
            'postal_code' => $profileArr['postal_code'],
            'country' => $profileArr['country'],
            'tax_id' => $profileArr['tax_id'],
            'ic_num' => $profileArr['ic_num'],
            'remarks' => $profileArr['remarks'],
        ], Employee::factory()->make([
            'code' => $company_1->employees()->inRandomOrder()->first()->code,
        ])->toArray());

        $api = $this->json('POST', route('api.post.db.company.employee.save', $employee_company_2->uuid), $employeeArr);

        $api->assertSuccessful();
    }

    public function test_employee_api_call_store_with_empty_string_parameters_expect_validation_error()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                ->has(Company::factory()->setIsDefault(), 'companies')
                ->create();

        $this->actingAs($user);

        $branchArr = [];
        $api = $this->json('POST', route('api.post.db.company.employee.save'), $branchArr);

        $api->assertJsonValidationErrors(['company_id', 'code', 'name']);
    }
    /* #endregion */

    /* #region list */
    public function test_employee_api_call_list_with_or_without_pagination_expect_paginator_or_collection()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(2), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $employeeSeeder = new EmployeeTableSeeder();
        $employeeSeeder->callWith(EmployeeTableSeeder::class, [3, $companyId]);

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.employee.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => '',
            'paginate' => true,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);

        $api = $this->getJson(route('api.get.db.company.employee.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => '',
            'paginate' => false,
            'page' => 1,
            'perPage' => 10,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
    }

    public function test_employee_api_call_list_with_search_expect_filtered_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                        ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $employeeSeeder = new EmployeeTableSeeder();
        $employeeSeeder->callWith(EmployeeTableSeeder::class, [6, $companyId]);

        $employees = $company->employees()->inRandomOrder()->take(3)->get();     
        for ($i = 0; $i < $employees->count(); $i++) {
            $employeeUser = $employees[$i]->user()->first();
            $employeeUser->update([
                'name' => $employeeUser->name . ' testing',
            ]);
        }

        $api = $this->getJson(route('api.get.db.company.employee.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => 'testing',
            'paginate' => true,
            'page' => 1,
            'perPage' => 3,
            'refresh' => true,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);

        $api->assertJsonFragment([
            'total' => 3,
        ]);
    }

    public function test_employee_api_call_list_without_search_querystring_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(2), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.employee.list', [
            'companyId' => Hashids::encode($companyId),
        ]));

        $api->assertStatus(422);
    }

    public function test_employee_api_call_list_with_special_char_in_search_expect_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.employee.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => " !#$%&'()*+,-./:;<=>?@[\]^_`{|}~",
            'paginate' => true,
            'page' => 1,
            'perPage' => 10,
            'refresh' => false,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }

    public function test_employee_api_call_list_with_negative_value_in_parameters_expect_results()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $this->actingAs($user);

        $api = $this->getJson(route('api.get.db.company.employee.list', [
            'companyId' => Hashids::encode($companyId),
            'search' => '',
            'paginate' => true,
            'page' => -1,
            'perPage' => -10,
            'refresh' => false,
        ]));

        $api->assertSuccessful();
        $api->assertJsonStructure([
            'data',
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta'=> [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }
    /* #endregion */

    /* #region read */
    public function test_employee_api_call_read_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(3), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $employeeSeeder = new EmployeeTableSeeder();
        $employeeSeeder->callWith(EmployeeTableSeeder::class, [3, $companyId]);

        $this->actingAs($user);

        $uuid = $company->employees()->inRandomOrder()->first()->uuid;

        $api = $this->getJson(route('api.get.db.company.employee.read', $uuid));

        $api->assertSuccessful();
    }

    public function test_employee_api_call_read_without_uuid_expect_exception()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $company = $user->companies->first();

        $this->actingAs($user);

        $this->getJson(route('api.get.db.company.employee.read', null));
    }

    public function test_employee_api_call_read_with_nonexistance_uuid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();


        $this->actingAs($user);

        $uuid = $this->faker->uuid();

        $api = $this->getJson(route('api.get.db.company.employee.read', $uuid));

        $api->assertStatus(404);
    }
    /* #endregion */

    /* #region update */
    public function test_employee_api_call_update_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $employeeSeeder = new EmployeeTableSeeder();
        $employeeSeeder->callWith(EmployeeTableSeeder::class, [3, $companyId]);

        $this->actingAs($user);

        $accessCount = $this->randomGenerator->generateNumber(1, $company->branches()->count());
        $branches = $company->branches()->inRandomOrder()->take($accessCount)->get();
        $accessBranchIds = [];
        for ($i = 0; $i < $accessCount; $i++) {
            array_push($accessBranchIds, Hashids::encode($branches[$i]->id));
        }

        $userArr = User::factory()->make()->toArray();
        
        $profileArr = Profile::factory()->make()->toArray();

        $employeeArr = array_merge([
            'company_id' => Hashids::encode($companyId),
            'name' => $userArr['name'],
            'address' => $profileArr['address'],
            'city' => $profileArr['city'],
            'postal_code' => $profileArr['postal_code'],
            'country' => $profileArr['country'],
            'tax_id' => $profileArr['tax_id'],
            'ic_num' => $profileArr['ic_num'],
            'remarks' => $profileArr['remarks'],
            'accessBranchIds' => $accessBranchIds,
        ], Employee::factory()->setStatusActive()->make()->toArray());

        $employee = $company->employees()->inRandomOrder()->first();

        $api = $this->json('POST', route('api.post.db.company.employee.edit', $employee->uuid), $employeeArr);

        $api->assertSuccessful();

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'company_id' => $companyId,
            'code' => $employeeArr['code'],
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $employeeArr['name'],
            'email' => $employee->user()->first()->email,
        ]);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $employee->user()->first()->id,
            'address' => $employeeArr['address'],
            'city' => $employeeArr['city'],
            'postal_code' => $employeeArr['postal_code'],
            'country' => $employeeArr['country'],
            'tax_id' => $employeeArr['tax_id'],
            'ic_num' => $employeeArr['ic_num'],
            'status' => $employee->user()->first()->profile()->first()->status->value,
            'remarks' => $employeeArr['remarks'],
        ]);

        for ($i = 0; $i < $accessCount ; $i++) {           
            $this->assertDatabaseHas('employee_accesses', [
                'employee_id' =>$employee->id,
                'branch_id' => Hashids::decode($accessBranchIds[$i])[0],
            ]);
        }
    }

    public function test_employee_api_call_update_and_use_existing_code_in_same_company_expect_failed()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                        ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $company = $user->companies->first();
        $companyId = $company->id;

        $employeeSeeder = new EmployeeTableSeeder();
        $employeeSeeder->callWith(EmployeeTableSeeder::class, [5, $companyId]);

        $this->actingAs($user);

        $employees = $company->employees()->inRandomOrder()->take(2)->get();
        $employee_1 = $employees[0];
        $employee_2 = $employees[1];

        $employeeArr = Employee::factory()->make([
            'code' => $employee_1->code,
        ])->toArray();

        $api = $this->json('POST', route('api.post.db.company.employee.edit', $employee_2->uuid), $employeeArr);

        $api->assertStatus(422);
        $api->assertJsonStructure([
            'errors',
        ]);
    }

    public function test_employee_api_call_update_and_use_existing_code_in_different_company_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->count(2)->setIsDefault()
                        ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();
        
        $this->actingAs($user);
        
        $employeeSeeder = new EmployeeTableSeeder();

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;
        $employeeSeeder->callWith(EmployeeTableSeeder::class, [5, $companyId_1]);
        $employee_company_1 = $company_1->employees()->inRandomOrder()->first();

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;
        $employeeSeeder->callWith(EmployeeTableSeeder::class, [5, $companyId_2]);
        $employee_company_2 = $company_2->employees()->inRandomOrder()->first();

        $accessCount = $this->randomGenerator->generateNumber(1, $company_2->branches()->count());
        $branches = $company_2->branches()->inRandomOrder()->take($accessCount)->get();
        $accessBranchIds = [];
        for ($i = 0; $i < $accessCount; $i++) {
            array_push($accessBranchIds, Hashids::encode($branches[$i]->id));
        }

        $userArr = User::factory()->make()->toArray();

        $profileArr = Profile::factory()->make()->toArray();

        $employeeArr = array_merge([
            'company_id' => Hashids::encode($companyId_2),
            'code' => $employee_company_1->code,
            'name' => $userArr['name'],
            'email' => $userArr['email'],
            'address' => $profileArr['address'],
            'city' => $profileArr['city'],
            'postal_code' => $profileArr['postal_code'],
            'country' => $profileArr['country'],
            'tax_id' => $profileArr['tax_id'],
            'ic_num' => $profileArr['ic_num'],
            'remarks' => $profileArr['remarks'],
            'accessBranchIds' => $accessBranchIds,
        ], Employee::factory()->setStatusActive()->make()->toArray());

        $api = $this->json('POST', route('api.post.db.company.employee.edit', $employee_company_2->uuid), $employeeArr);

        $api->assertSuccessful();
    }
    /* #endregion */

    /* #region delete */
    public function test_employee_api_call_delete_expect_successful()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()
                    ->hasAttached(Role::where('name', '=', UserRoles::DEVELOPER->value)->first())
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $this->actingAs($user);

        $company = $user->companies->first();
        $companyId = $company->id;

        $employeeSeeder = new EmployeeTableSeeder();
        $employeeSeeder->callWith(EmployeeTableSeeder::class, [3, $companyId]);
        
        $employee = $company->employees()->inRandomOrder()->first();

        $api = $this->json('POST', route('api.post.db.company.employee.delete', $employee->uuid));

        $api->assertSuccessful();
        $this->assertSoftDeleted('employees', [
            'id' => $employee->id,
        ]);
    }

    public function test_employee_api_call_delete_of_nonexistance_uuid_expect_not_found()
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $uuid = $this->faker->uuid();

        $api = $this->json('POST', route('api.post.db.company.employee.delete', $uuid));

        $api->assertStatus(404);
    }

    public function test_employee_api_call_delete_without_parameters_expect_failed()
    {
        $this->expectException(Exception::class);
        /** @var \Illuminate\Contracts\Auth\Authenticatable */
        $user = User::factory()->create();

        $this->actingAs($user);
        $api = $this->json('POST', route('api.post.db.company.employee.delete', null));
    }
    /* #endregion */

    /* #region others */

    /* #endregion */
}
