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
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class EmployeeActionsDeleteTest extends TestCase
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

    public function test_employee_actions_call_delete_expect_bool()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->branchSeeder->callWith(BranchTableSeeder::class, [5, $companyId]);
        $this->employeeSeeder->callWith(EmployeeTableSeeder::class, [5, $companyId]);

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

            $this->employeeActions->create(
                $employeeArr,
                $employeeUserArr,
                $profileArr,
                $accessesArr
            );
        }

        $employee = $user->companies()->inRandomOrder()->first()->employees()->inRandomOrder()->first();

        $result = $this->employeeActions->delete($employee);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('employees', [
            'id' => $employee->id,
        ]);
    }
}
