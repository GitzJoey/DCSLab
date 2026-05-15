<?php

namespace Tests\Unit\Actions\BranchActions;

use App\Actions\Branch\BranchActions;
use App\DTOs\BranchUpdateDTO;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use ArgumentCountError;
use Tests\ActionsTestCase;

class BranchActionsEditTest extends ActionsTestCase
{
    private BranchActions $branchActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branchActions = new BranchActions();
    }

    public function test_branch_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $branch = $company->branches()->inRandomOrder()->first();

        $payload = Branch::factory()->make()->toArray();

        $dto = new BranchUpdateDTO(
            code: $payload['code'],
            name: $payload['name'],
            address: $payload['address'],
            city: $payload['city'],
            contact: $payload['contact'],
            isMain: $payload['is_main'],
            remarks: $payload['remarks'],
            status: $payload['status']
        );

        $result = $this->branchActions->update($branch, $dto);
        $this->assertInstanceOf(Branch::class, $result);
        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'company_id' => $branch->company_id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_branch_actions_call_update_with_is_main_expect_other_branches_reset()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->count(2)->state([
                    'is_main' => false,
                ]))
            )
            ->create();

        $company = $user->companies()->first();
        $branches = $company->branches()->orderBy('id')->take(2)->get();
        $previousMainBranch = $branches[0];
        $previousMainBranch->update(['is_main' => true]);
        $targetBranch = $branches[1];
        $payload = Branch::factory()->setStatusActive()->setIsMainBranch()->make()->toArray();

        $dto = new BranchUpdateDTO(
            code: $payload['code'],
            name: $payload['name'],
            address: $payload['address'],
            city: $payload['city'],
            contact: $payload['contact'],
            isMain: true,
            remarks: $payload['remarks'],
            status: $payload['status'],
        );

        $result = $this->branchActions->update($targetBranch, $dto);
        $this->assertTrue((bool) $result->is_main);
        $this->assertDatabaseHas('branches', [
            'id' => $previousMainBranch->id,
            'is_main' => false,
        ]);
    }

    public function test_branch_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(ArgumentCountError::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
            )->create();

        $branch = $user->companies()->inRandomOrder()->first()
            ->branches()->inRandomOrder()->first();

        $dto = new BranchUpdateDTO(...[]);

        $this->branchActions->update($branch, $dto);
    }
}
