<?php

namespace Tests\Feature\Service;

use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Profile;
use App\Models\Employee;
use Tests\ServiceTestCase;
use App\Services\EmployeeService;
use Database\Seeders\BranchTableSeeder;
use Database\Seeders\EmployeeTableSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Factories\Sequence;

class EmployeeServiceTest extends ServiceTestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employeeService = app(EmployeeService::class);
    }

    /* #region create */
    public function test_employee_service_call_create_expect_db_has_record()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(20), 'branches'), 'companies')
                    ->create();
        
        $companyId = $user->companies->first()->id;

        $employeeUser = User::factory()->make([]);
        $employeeUser = $employeeUser->toArray();
        $employeeUser['password'] = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

        $employeeArr = Employee::factory()->make([
            'company_id' => $companyId,
            'user_id' => $user->id
        ]);

        $profileArr = Profile::factory()->make([
            'user_id' => $user->id
        ]);

        $branchCount = Company::find($companyId)->branches->count();
        $accessCount = $this->faker->numberBetween(1, $branchCount);
        $branchIds = Company::find($companyId)->branches()->inRandomOrder()->take($accessCount)->pluck('id');
        $accessesArr = [];
        for ($i = 0; $i < $accessCount ; $i++) {
            array_push($accessesArr, array(
                'branch_id' => $branchIds[$i]
            ));
        }

        $result = $this->employeeService->create(
            $employeeArr->toArray(),
            $employeeUser,
            $profileArr->toArray(),
            $accessesArr
        );

        $employeeId = $result->id;

        $this->assertDatabaseHas('employees', [
            'id' => $employeeId,
            'company_id' => $employeeArr['company_id'],
            'code' => $employeeArr['code']
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $employeeUser['name'],
            'email' => $employeeUser['email']
        ]);

        $this->assertDatabaseHas('profiles', [
            'address' => $profileArr['address'],
            'city' => $profileArr['city'],
            'postal_code' => $profileArr['postal_code'],
            'country' => $profileArr['country'],
            'tax_id' => $profileArr['tax_id'],
            'ic_num' => $profileArr['ic_num'],
            'status' => $profileArr['status'],
            'remarks' => $profileArr['remarks']
        ]);

        for ($i = 0; $i < $accessCount ; $i++) {           
            $this->assertDatabaseHas('employee_accesses', [
                'employee_id' => $employeeId,
                'branch_id' => $branchIds[$i]
            ]);
        }
    }

    public function test_employee_service_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->employeeService->create(
            [],
            [],
            [],
            [],
        );
    }
    /* #endregion */

    /* #region list */
    public function test_employee_service_call_list_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $result = $this->employeeService->list(
            companyId: $user->companies->first()->id,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_employee_service_call_list_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $result = $this->employeeService->list(
            companyId: $user->companies->first()->id,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_employee_service_call_list_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Employee::max('id') + 1;
        $result = $this->employeeService->list(
            companyId: $maxId,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_employee_service_call_list_with_search_parameter_expect_filtered_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(20), 'branches'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        for ($n = 0; $n < 2; $n++) {
            for ($i = 0; $i < 10; $i++) {
                $employeeUserArr = User::factory()->make();
                $employeeUserArr->created_at = Carbon::now();
                $employeeUserArr->updated_at = Carbon::now();
                $employeeUserArr = $employeeUserArr->toArray();
                $employeeUserArr['name'] = $n == 1 ? $employeeUserArr['name'] . 'testing' : $employeeUserArr['name'];
                $employeeUserArr['password'] = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

                $employeeArr = Employee::factory()->make([
                    'company_id' => $companyId,
                    'name' => $employeeUserArr['name'],
                ])->toArray();

                $first_name = '';
                $last_name = '';
                if ($employeeUserArr['name'] == trim($employeeUserArr['name']) && strpos($employeeUserArr['name'], ' ') !== false) {
                    $pieces = explode(" ", $employeeUserArr['name']);
                    $first_name = $pieces[0];
                    $last_name = $pieces[1];
                } else {
                    $first_name = $employeeUserArr['name'];
                }
        
                $profileArr = Profile::factory()->make([
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                ])->toArray();
        
                $branchCount = Company::find($companyId)->branches->count();
                $accessCount = $this->faker->numberBetween(1, $branchCount);
                $branchIds = Company::find($companyId)->branches()->inRandomOrder()->take($accessCount)->pluck('id');
                $accessesArr = [];
                for ($x = 0; $x < $accessCount ; $x++) {
                    array_push($accessesArr, array(
                        'branch_id' => $branchIds[$x]
                    ));
                }
    
                $this->employeeService->create(
                    $employeeArr,
                    $employeeUserArr,
                    $profileArr,
                    $accessesArr
                );
            }
        }

        $result = $this->employeeService->list(
            companyId: $companyId, 
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 10);
    }

    public function test_employee_service_call_list_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(20), 'branches'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        for ($i = 0; $i < 25; $i++) {
            $employeeUserArr = User::factory()->make();
            $employeeUserArr->created_at = Carbon::now();
            $employeeUserArr->updated_at = Carbon::now();
            $employeeUserArr = $employeeUserArr->toArray();
            $employeeUserArr['password'] = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

            $employeeArr = Employee::factory()->make([
                'company_id' => $companyId,
                'name' => $employeeUserArr['name'],
            ])->toArray();

            $first_name = '';
            $last_name = '';
            if ($employeeUserArr['name'] == trim($employeeUserArr['name']) && strpos($employeeUserArr['name'], ' ') !== false) {
                $pieces = explode(" ", $employeeUserArr['name']);
                $first_name = $pieces[0];
                $last_name = $pieces[1];
            } else {
                $first_name = $employeeUserArr['name'];
            }
    
            $profileArr = Profile::factory()->make([
                'first_name' => $first_name,
                'last_name' => $last_name,
            ])->toArray();
    
            $branchCount = Company::find($companyId)->branches->count();
            $accessCount = $this->faker->numberBetween(1, $branchCount);
            $branchIds = Company::find($companyId)->branches()->inRandomOrder()->take($accessCount)->pluck('id');
            $accessesArr = [];
            for ($x = 0; $x < $accessCount ; $x++) {
                array_push($accessesArr, array(
                    'branch_id' => $branchIds[$x]
                ));
            }

            $this->employeeService->create(
                $employeeArr,
                $employeeUserArr,
                $profileArr,
                $accessesArr
            );
        }

        $result = $this->employeeService->list(
            companyId: $companyId, 
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 25);
    }

    public function test_employee_service_call_list_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(20), 'branches'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        for ($i = 0; $i < 25; $i++) {
            $employeeUserArr = User::factory()->make();
            $employeeUserArr->created_at = Carbon::now();
            $employeeUserArr->updated_at = Carbon::now();
            $employeeUserArr = $employeeUserArr->toArray();
            $employeeUserArr['password'] = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

            $employeeArr = Employee::factory()->make([
                'company_id' => $companyId,
                'name' => $employeeUserArr['name'],
            ])->toArray();

            $first_name = '';
            $last_name = '';
            if ($employeeUserArr['name'] == trim($employeeUserArr['name']) && strpos($employeeUserArr['name'], ' ') !== false) {
                $pieces = explode(" ", $employeeUserArr['name']);
                $first_name = $pieces[0];
                $last_name = $pieces[1];
            } else {
                $first_name = $employeeUserArr['name'];
            }
    
            $profileArr = Profile::factory()->make([
                'first_name' => $first_name,
                'last_name' => $last_name,
            ])->toArray();
    
            $branchCount = Company::find($companyId)->branches->count();
            $accessCount = $this->faker->numberBetween(1, $branchCount);
            $branchIds = Company::find($companyId)->branches()->inRandomOrder()->take($accessCount)->pluck('id');
            $accessesArr = [];
            for ($x = 0; $x < $accessCount ; $x++) {
                array_push($accessesArr, array(
                    'branch_id' => $branchIds[$x]
                ));
            }

            $this->employeeService->create(
                $employeeArr,
                $employeeUserArr,
                $profileArr,
                $accessesArr
            );
        }

        $result = $this->employeeService->list(
            companyId: $companyId, 
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 25);
    }
    /* #endregion */

    /* #region read */
    public function test_employee_service_call_read_expect_object()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(20), 'branches'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        $employeeUserArr = User::factory()->make();
        $employeeUserArr->created_at = Carbon::now();
        $employeeUserArr->updated_at = Carbon::now();
        $employeeUserArr = $employeeUserArr->toArray();
        $employeeUserArr['password'] = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

        $employeeArr = Employee::factory()->make([
            'company_id' => $companyId,
            'name' => $employeeUserArr['name'],
        ])->toArray();

        $first_name = '';
        $last_name = '';
        if ($employeeUserArr['name'] == trim($employeeUserArr['name']) && strpos($employeeUserArr['name'], ' ') !== false) {
            $pieces = explode(" ", $employeeUserArr['name']);
            $first_name = $pieces[0];
            $last_name = $pieces[1];
        } else {
            $first_name = $employeeUserArr['name'];
        }

        $profileArr = Profile::factory()->make([
            'first_name' => $first_name,
            'last_name' => $last_name,
        ])->toArray();

        $branchCount = Company::find($companyId)->branches->count();
        $accessCount = $this->faker->numberBetween(1, $branchCount);
        $branchIds = Company::find($companyId)->branches()->inRandomOrder()->take($accessCount)->pluck('id');
        $accessesArr = [];
        for ($x = 0; $x < $accessCount ; $x++) {
            array_push($accessesArr, array(
                'branch_id' => $branchIds[$x]
            ));
        }

        $employee = $this->employeeService->create(
            $employeeArr,
            $employeeUserArr,
            $profileArr,
            $accessesArr
        );

        $result = $this->employeeService->read($employee);

        $this->assertInstanceOf(Employee::class, $result);
    }
    /* #endregion */

    /* #region update */
    public function test_employee_service_call_update_expect_db_updated()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(20), 'branches'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        $employeeUserArr = User::factory()->make();
        $employeeUserArr->created_at = Carbon::now();
        $employeeUserArr->updated_at = Carbon::now();
        $employeeUserArr = $employeeUserArr->toArray();
        $employeeUserArr['password'] = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

        $employeeArr = Employee::factory()->make([
            'company_id' => $companyId,
            'name' => $employeeUserArr['name'],
        ])->toArray();

        $first_name = '';
        $last_name = '';
        if ($employeeUserArr['name'] == trim($employeeUserArr['name']) && strpos($employeeUserArr['name'], ' ') !== false) {
            $pieces = explode(" ", $employeeUserArr['name']);
            $first_name = $pieces[0];
            $last_name = $pieces[1];
        } else {
            $first_name = $employeeUserArr['name'];
        }

        $profileArr = Profile::factory()->make([
            'first_name' => $first_name,
            'last_name' => $last_name,
        ])->toArray();

        $branchCount = Company::find($companyId)->branches->count();
        $accessCount = $this->faker->numberBetween(1, $branchCount);
        $branchIds = Company::find($companyId)->branches()->inRandomOrder()->take($accessCount)->pluck('id');
        $accessesArr = [];
        for ($x = 0; $x < $accessCount ; $x++) {
            array_push($accessesArr, array(
                'branch_id' => $branchIds[$x]
            ));
        }

        $employee = $this->employeeService->create(
            $employeeArr,
            $employeeUserArr,
            $profileArr,
            $accessesArr
        );

        $newEmployeeUserArr = User::factory()->make();
        $newEmployeeUserArr->created_at = Carbon::now();
        $newEmployeeUserArr->updated_at = Carbon::now();
        $newEmployeeUserArr = $newEmployeeUserArr->toArray();
        $newEmployeeUserArr['password'] = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

        $newEmployeeArr = Employee::factory()->make([
            'company_id' => $companyId,
            'name' => $newEmployeeUserArr['name'],
        ])->toArray();

        $new_first_name = '';
        $new_last_name = '';
        if ($newEmployeeUserArr['name'] == trim($newEmployeeUserArr['name']) && strpos($newEmployeeUserArr['name'], ' ') !== false) {
            $pieces = explode(" ", $newEmployeeUserArr['name']);
            $new_first_name = $pieces[0];
            $new_last_name = $pieces[1];
        } else {
            $new_first_name = $newEmployeeUserArr['name'];
        }

        $newProfileArr = Profile::factory()->make([
            'first_name' => $new_first_name,
            'last_name' => $new_last_name,
        ])->toArray();

        $newBranchCount = Company::find($companyId)->branches->count();
        $newAccessCount = $this->faker->numberBetween(1, $newBranchCount);
        $newBranchIds = Company::find($companyId)->branches()->inRandomOrder()->take($newAccessCount)->pluck('id');
        $newAccessesArr = [];
        for ($x = 0; $x < $newAccessCount ; $x++) {
            array_push($newAccessesArr, array(
                'branch_id' => $newBranchIds[$x]
            ));
        }

        $result = $this->employeeService->update(
            $employee,
            $newEmployeeArr,
            $newEmployeeUserArr,
            $newProfileArr,
            $newAccessesArr
        );

        $this->assertInstanceOf(Employee::class, $result);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'company_id' => $newEmployeeArr['company_id'],
            'code' => $newEmployeeArr['code']
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $newEmployeeUserArr['name'],
            'email' => $employeeUserArr['email']
        ]);

        $this->assertDatabaseHas('profiles', [
            'address' => $newProfileArr['address'],
            'city' => $newProfileArr['city'],
            'postal_code' => $newProfileArr['postal_code'],
            'country' => $newProfileArr['country'],
            'tax_id' => $newProfileArr['tax_id'],
            'ic_num' => $newProfileArr['ic_num'],
            'status' => $newProfileArr['status'],
            'remarks' => $newProfileArr['remarks']
        ]);

        for ($i = 0; $i < $newAccessCount ; $i++) {           
            $this->assertDatabaseHas('employee_accesses', [
                'employee_id' =>$employee->id,
                'branch_id' => $newBranchIds[$i]
            ]);
        }
    }

    public function test_employee_service_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                            ->has(Branch::factory()->count(20), 'branches'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        $employeeUserArr = User::factory()->make();
        $employeeUserArr->created_at = Carbon::now();
        $employeeUserArr->updated_at = Carbon::now();
        $employeeUserArr = $employeeUserArr->toArray();
        $employeeUserArr['password'] = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

        $employeeArr = Employee::factory()->make([
            'company_id' => $companyId,
            'name' => $employeeUserArr['name'],
        ])->toArray();

        $first_name = '';
        $last_name = '';
        if ($employeeUserArr['name'] == trim($employeeUserArr['name']) && strpos($employeeUserArr['name'], ' ') !== false) {
            $pieces = explode(" ", $employeeUserArr['name']);
            $first_name = $pieces[0];
            $last_name = $pieces[1];
        } else {
            $first_name = $employeeUserArr['name'];
        }

        $profileArr = Profile::factory()->make([
            'first_name' => $first_name,
            'last_name' => $last_name,
        ])->toArray();

        $branchCount = Company::find($companyId)->branches->count();
        $accessCount = $this->faker->numberBetween(1, $branchCount);
        $branchIds = Company::find($companyId)->branches()->inRandomOrder()->take($accessCount)->pluck('id');
        $accessesArr = [];
        for ($x = 0; $x < $accessCount ; $x++) {
            array_push($accessesArr, array(
                'branch_id' => $branchIds[$x]
            ));
        }

        $employee = $this->employeeService->create(
            $employeeArr,
            $employeeUserArr,
            $profileArr,
            $accessesArr
        );

        $newEmployeeArr = [];
        $newEmployeeUserArr = [];
        $newProfileArr = [];
        $newAccessesArr = [];

        $this->employeeService->update(
            $employee,
            $newEmployeeArr,
            $newEmployeeUserArr,
            $newProfileArr,
            $newAccessesArr
        );
    }
    /* #endregion */

    /* #region delete */
    public function test_employee_service_call_delete_expect_bool()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(Branch::factory()->count(5), 'branches'), 'companies')
                    ->create();

        $companyId = $user->companies->first()->id;

        for ($i = 0; $i < 25; $i++) {
            $employeeUserArr = User::factory()->make();
            $employeeUserArr->created_at = Carbon::now();
            $employeeUserArr->updated_at = Carbon::now();
            $employeeUserArr = $employeeUserArr->toArray();
            $employeeUserArr['password'] = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

            $employeeArr = Employee::factory()->make([
                'company_id' => $companyId,
                'name' => $employeeUserArr['name'],
            ])->toArray();

            $first_name = '';
            $last_name = '';
            if ($employeeUserArr['name'] == trim($employeeUserArr['name']) && strpos($employeeUserArr['name'], ' ') !== false) {
                $pieces = explode(" ", $employeeUserArr['name']);
                $first_name = $pieces[0];
                $last_name = $pieces[1];
            } else {
                $first_name = $employeeUserArr['name'];
            }
    
            $profileArr = Profile::factory()->make([
                'first_name' => $first_name,
                'last_name' => $last_name,
            ])->toArray();
    
            $branchCount = Company::find($companyId)->branches->count();
            $accessCount = $this->faker->numberBetween(1, $branchCount);
            $branchIds = Company::find($companyId)->branches()->inRandomOrder()->take($accessCount)->pluck('id');
            $accessesArr = [];
            for ($x = 0; $x < $accessCount ; $x++) {
                array_push($accessesArr, array(
                    'branch_id' => $branchIds[$x]
                ));
            }

            $this->employeeService->create(
                $employeeArr,
                $employeeUserArr,
                $profileArr,
                $accessesArr
            );
        }

        $employee = $user->companies()->inRandomOrder()->first()->employees()->inRandomOrder()->first();

        $result = $this->employeeService->delete($employee);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('employees', [
            'id' => $employee->id,
        ]);
    }
    /* #endregion */

    /* #region others */
    public function test_employee_service_call_function_generateUniqueCode_expect_unique_code_returned()
    {
        $user = User::factory()
            ->has(Company::factory()->setIsDefault(), 'companies')
            ->create();
        
        $company = $user->companies->first();
        $companyId = $company->id;

        $branchSeeder = new BranchTableSeeder();
        $branchSeeder->callWith(BranchTableSeeder::class, [1, $companyId]);
        
        $branchId = $company->branches()->first()->id;

        $employeeSeeder = new EmployeeTableSeeder();
        $employeeSeeder->callWith(EmployeeTableSeeder::class, [1, $companyId, $branchId]);
        
        $code = $this->employeeService->generateUniqueCode();

        $this->assertIsString($code);
        
        $resultCount = $company->employees()->where('code', '=', $code)->count();
        $this->assertTrue($resultCount == 0);
    }

    public function test_employee_service_call_function_isUniqueCode_expect_can_detect_unique_code()
    {
        $branchSeeder = new BranchTableSeeder();
        $employeeSeeder = new EmployeeTableSeeder();

        $user = User::factory()
                    ->has(Company::factory()->count(2)->state(new Sequence(['default' => true], ['default' => false])), 'companies')
                    ->create();

        $company_1 = $user->companies[0];
        $companyId_1 = $company_1->id;

        $branchSeeder->callWith(BranchTableSeeder::class, [1, $companyId_1]);
        $branchId_company_1 = $company_1->branches()->inRandomOrder()->first()->id;

        $employeeSeeder->callWith(EmployeeTableSeeder::class, [1, $companyId_1, $branchId_company_1]);
        $employee_company_1_code = $company_1->employees()->inRandomOrder()->first()->code;

        $company_2 = $user->companies[1];
        $companyId_2 = $company_2->id;

        $branchSeeder->callWith(BranchTableSeeder::class, [1, $companyId_2]);
        $branchId_company_2 = $company_2->branches()->inRandomOrder()->first()->id;

        $employeeSeeder->callWith(EmployeeTableSeeder::class, [1, $companyId_2, $branchId_company_2]);
        $employee_company_2_code = $company_2->employees()->inRandomOrder()->first()->code;

        $this->assertFalse($this->employeeService->isUniqueCode($employee_company_1_code, $companyId_1));
        $this->assertTrue($this->employeeService->isUniqueCode('test2', $companyId_1));
        $this->assertTrue($this->employeeService->isUniqueCode('test3', $companyId_1));
        $this->assertFalse($this->employeeService->isUniqueCode($employee_company_2_code, $companyId_2));
        $this->assertTrue($this->employeeService->isUniqueCode('test1', $companyId_2));
    }
    /* #endregion */
}
