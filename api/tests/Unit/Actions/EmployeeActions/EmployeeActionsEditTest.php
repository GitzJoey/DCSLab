<?php

namespace Tests\Unit\Actions\EmployeeActions;

use App\Actions\Employee\EmployeeActions;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class EmployeeActionsEditTest extends ActionsTestCase
{
    private EmployeeActions $employeeActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employeeActions = new EmployeeActions();
    }

    public function test_employee_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Employee::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $employee = $company->employees()->inRandomOrder()->first();

        $employeeArr = Employee::factory()->make()->toArray();

        $result = $this->employeeActions->update($employee, $employeeArr);

        $this->assertInstanceOf(Employee::class, $result);
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'company_id' => $employee->company_id,
            'code' => $employeeArr['code'],
            'name' => $employeeArr['name'],
        ]);
    }

    public function test_employee_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Employee::factory())
            )->create();

        $employee = $user->companies()->inRandomOrder()->first()
            ->employees()->inRandomOrder()->first();

        $employeeArr = [];

        $this->employeeActions->update($employee, $employeeArr);
    }
}
