<?php

namespace Tests\Unit\Actions\BranchActions;

use App\Actions\Branch\BranchActions;
use App\DTOs\BranchCreateDTO;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use ArgumentCountError;
use Tests\ActionsTestCase;

class BranchActionsCreateTest extends ActionsTestCase
{
    private BranchActions $branchActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branchActions = new BranchActions();
    }

    public function test_branch_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $payload = Branch::factory()->for($company)
            ->setStatusActive()->setIsMainBranch()
            ->make()->toArray();

        $dto = new BranchCreateDTO(
            companyId: $payload['company_id'],
            code: $payload['code'],
            name: $payload['name'],
            address: $payload['address'],
            city: $payload['city'],
            contact: $payload['contact'],
            isMain: $payload['is_main'],
            remarks: $payload['remarks'],
            status: $payload['status']
        );

        $result = $this->branchActions->create($dto);
        $this->assertDatabaseHas('branches', [
            'id' => $result->id,
            'company_id' => $payload['company_id'],
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_branch_actions_call_create_with_is_main_expect_other_branches_reset()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->state(['is_main' => true]))
            )
            ->create();

        $company = $user->companies()->first();
        $previousMainBranch = $company->branches()->first();
        $payload = Branch::factory()->for($company)
            ->setStatusActive()
            ->setIsMainBranch()
            ->make()
            ->toArray();

        $dto = new BranchCreateDTO(
            companyId: $payload['company_id'],
            code: $payload['code'],
            name: $payload['name'],
            address: $payload['address'],
            city: $payload['city'],
            contact: $payload['contact'],
            isMain: true,
            remarks: $payload['remarks'],
            status: $payload['status'],
        );

        $result = $this->branchActions->create($dto);
        $this->assertTrue((bool) $result->is_main);
        $this->assertDatabaseHas('branches', [
            'id' => $previousMainBranch->id,
            'is_main' => false,
        ]);
    }

    public function test_branch_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(ArgumentCountError::class);
        $dto = new BranchCreateDTO(...[]);

        $this->branchActions->create($dto);
    }
}
