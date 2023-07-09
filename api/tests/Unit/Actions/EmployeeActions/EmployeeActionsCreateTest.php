<?php

namespace Tests\Unit\Actions\EmployeeActions;

use App\Actions\Employee\EmployeeActions;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Profile;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Factories\Sequence;
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
        $branchCount = 5;
        $idxMainBranch = random_int(0, $branchCount - 1);

        $user = User::factory()
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

        $employeeArr = Employee::factory()->for($company)->for($user)->make()->toArray();
        $employeeUserArr = User::factory()->make()->toArray();
        $employeeUserArr['password'] = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
        $profileArr = Profile::factory()->for($user)->setStatusActive()->make()->toArray();

        $branchCount = $company->branches()->count();
        $accessCount = random_int(1, $branchCount);
        $branchIds = $company->branches()->inRandomOrder()->take($accessCount)->pluck('id');
        $accessesArr = [];
        for ($i = 0; $i < $accessCount; $i++) {
            array_push($accessesArr, [
                'branch_id' => $branchIds[$i],
            ]);
        }

        $result = $this->employeeActions->create(
            $employeeArr,
            $employeeUserArr,
            $profileArr,
            $accessesArr
        );

        $employeeId = $result->id;

        $this->assertDatabaseHas('employees', [
            'id' => $employeeId,
            'company_id' => $employeeArr['company_id'],
            'code' => $employeeArr['code'],
        ]);

        $this->assertDatabaseHas('users', [
            'name' => $employeeUserArr['name'],
            'email' => $employeeUserArr['email'],
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
