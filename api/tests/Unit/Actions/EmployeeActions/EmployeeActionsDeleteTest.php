<?php

namespace Tests\Unit\Actions\EmployeeActions;

use App\Actions\Employee\EmployeeActions;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeAccess;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class EmployeeActionsDeleteTest extends ActionsTestCase
{
    private EmployeeActions $employeeActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employeeActions = new EmployeeActions();
    }

    public function test_employee_actions_call_delete_expect_bool()
    {
        $branchCount = 5;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
            ->has(Profile::factory()->setStatusActive())
            ->has(Company::factory()->setStatusActive()
                ->has(Branch::factory()->setStatusActive()->count($branchCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'is_main' => $sequence->index == $idxMainBranch ? true : false,
                        ]
                    ))
                ))
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $employee = Employee::factory()->for($company)->for($user)->create();

        $branchCount = $company->branches()->count();
        $accessCount = random_int(1, $branchCount);
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
