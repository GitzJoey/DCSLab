<?php

namespace Tests\Unit\Actions\EmployeeActions;

use App\Actions\Employee\EmployeeActions;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class EmployeeActionsCreateTest extends ActionsTestCase
{
    private EmployeeActions $employeeActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employeeActions = new EmployeeActions();
    }

    public function test_employee_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $employeeArr = Employee::factory()->for($company)
            ->make()->toArray();

        $result = $this->employeeActions->create($employeeArr);

        $this->assertDatabaseHas('employees', [
            'id' => $result->id,
            'company_id' => $employeeArr['company_id'],
            'code' => $employeeArr['code'],
            'name' => $employeeArr['name'],
        ]);
    }

    public function test_employee_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->employeeActions->create([]);
    }
}
