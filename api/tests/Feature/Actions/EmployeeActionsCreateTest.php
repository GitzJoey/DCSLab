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
use Tests\TestCase;

class EmployeeActionsCreateTest extends TestCase
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

    public function test_employee_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [5, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->branchSeeder->callWith(BranchTableSeeder::class, [3, $companyId]);

        $employeeUser = User::factory()->make([]);
        $employeeUser = $employeeUser->toArray();
        $employeeUser['password'] = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

        $employeeArr = Employee::factory()->make([
            'company_id' => $companyId,
            'user_id' => $user->id,
        ]);

        $profileArr = Profile::factory()->make([
            'user_id' => $user->id,
        ]);

        $branchCount = Company::find($companyId)->branches->count();
        $accessCount = $this->faker->numberBetween(1, $branchCount);
        $branchIds = Company::find($companyId)->branches()->inRandomOrder()->take($accessCount)->pluck('id');
        $accessesArr = [];
        for ($i = 0; $i < $accessCount; $i++) {
            array_push($accessesArr, [
                'branch_id' => $branchIds[$i],
            ]);
        }

        $result = $this->employeeActions->create(
            $employeeArr->toArray(),
            $employeeUser,
            $profileArr->toArray(),
            $accessesArr
        );

        $employeeId = $result->id;

        $this->assertDatabaseHas('employees', [
            'id' => $employeeId,
            'company_id' => $employeeArr['company_id'],
            'code' => $employeeArr['code'],
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $employeeUser['name'],
            'email' => $employeeUser['email'],
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

        for ($i = 0; $i < $accessCount; $i++) {
            $this->assertDatabaseHas('employee_accesses', [
                'employee_id' => $employeeId,
                'branch_id' => $branchIds[$i],
            ]);
        }
    }

    public function test_employee_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->employeeActions->create(
            [],
            [],
            [],
            [],
        );
    }
}
