<?php

namespace Tests\Feature;

use App\Actions\Employee\EmployeeActions;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeAccess;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmployeeActionsDeleteTest extends TestCase
{
    use WithFaker;
    
    private EmployeeActions $employeeActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employeeActions = new EmployeeActions();
    }

    public function test_employee_actions_call_delete_expect_bool()
    {
        $user = User::factory()
                ->has(Profile::factory()->setStatusActive())
                ->has(Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                    ->has(Branch::factory()->setStatusActive()->count(4))
                )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $employee = Employee::factory()->for($company)->for($user)->create();

        $branchCount = $company->branches()->count();
        $accessCount = $this->faker->numberBetween(1, $branchCount);
        $branches = $company->branches()->inRandomOrder()->take($accessCount)->get();
        for ($i = 0; $i < $accessCount; $i++) {
            EmployeeAccess::factory()
                ->for($company)->for($employee)->for($branches[$i])
            ->create();
        }

        $result = $this->employeeActions->delete($employee);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('employees', [
            'id' => $employee->id,
        ]);
    }
}
