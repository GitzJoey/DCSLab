<?php

namespace Tests\Feature;

use App\Actions\Employee\EmployeeActions;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\BranchTableSeeder;
use Database\Seeders\CompanyTableSeeder;
use Database\Seeders\EmployeeTableSeeder;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EmployeeActionsEditTest extends TestCase
{
    use WithFaker;

    private $employeeActions;

    private $companySeeder;

    private $branchSeeder;

    private $employeeSeeder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employeeActions = app(EmployeeActions::class);

        $this->companySeeder = new CompanyTableSeeder();
        $this->branchSeeder = new BranchTableSeeder();
        $this->employeeSeeder = new EmployeeTableSeeder();
    }

    public function test_employee_actions_call_update_expect_db_updated()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [5, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->branchSeeder->callWith(BranchTableSeeder::class, [3, $companyId]);

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
            $pieces = explode(' ', $employeeUserArr['name']);
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
        for ($x = 0; $x < $accessCount; $x++) {
            array_push($accessesArr, [
                'branch_id' => $branchIds[$x],
            ]);
        }

        $employee = $this->employeeActions->create(
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
            $pieces = explode(' ', $newEmployeeUserArr['name']);
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
        for ($x = 0; $x < $newAccessCount; $x++) {
            array_push($newAccessesArr, [
                'branch_id' => $newBranchIds[$x],
            ]);
        }

        $result = $this->employeeActions->update(
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
            'code' => $newEmployeeArr['code'],
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $newEmployeeUserArr['name'],
            'email' => $employeeUserArr['email'],
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

        for ($i = 0; $i < $newAccessCount; $i++) {
            $this->assertDatabaseHas('employee_accesses', [
                'employee_id' => $employee->id,
                'branch_id' => $newBranchIds[$i],
            ]);
        }
    }

    public function test_employee_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->branchSeeder->callWith(BranchTableSeeder::class, [20, $companyId]);

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
            $pieces = explode(' ', $employeeUserArr['name']);
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
        for ($x = 0; $x < $accessCount; $x++) {
            array_push($accessesArr, [
                'branch_id' => $branchIds[$x],
            ]);
        }

        $employee = $this->employeeActions->create(
            $employeeArr,
            $employeeUserArr,
            $profileArr,
            $accessesArr
        );

        $newEmployeeArr = [];
        $newEmployeeUserArr = [];
        $newProfileArr = [];
        $newAccessesArr = [];

        $this->employeeActions->update(
            $employee,
            $newEmployeeArr,
            $newEmployeeUserArr,
            $newProfileArr,
            $newAccessesArr
        );
    }
}
