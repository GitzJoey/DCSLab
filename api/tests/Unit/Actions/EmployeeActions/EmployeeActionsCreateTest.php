<?php

namespace Tests\Unit\Actions\EmployeeActions;

use App\Actions\Employee\EmployeeActions;
use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
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

        $payload = Employee::factory()->for($company)
            ->make()->toArray();

        $dto = new \App\DTOs\EmployeeCreateDTO(
            companyId: $payload['company_id'],
            code: $payload['code'],
            name: $payload['name'],
            remarks: $payload['remarks']
        );

        $result = $this->employeeActions->create($dto);
        $this->assertDatabaseHas('employees', [
            'id' => $result->id,
            'company_id' => $payload['company_id'],
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_employee_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $dto = new \App\DTOs\EmployeeCreateDTO();

        $this->employeeActions->create($dto);
    }
}
