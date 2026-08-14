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

        $payload = Employee::factory()->make()->toArray();

        $dto = new \App\DTOs\EmployeeUpdateDTO(
            companyId: $payload['company_id'],
            code: $payload['code'],
            name: $payload['name'],
            remarks: $payload['remarks']
        );

        $result = $this->employeeActions->update($employee, $dto);
        $this->assertInstanceOf(Employee::class, $result);
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'company_id' => $employee->company_id,
            'code' => $payload['code'],
            'name' => $payload['name'],
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

        $payload = [];

        $dto = new \App\DTOs\EmployeeUpdateDTO();

        $this->employeeActions->update($employee, $dto);
    }
}
