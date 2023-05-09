<?php

namespace Tests\Feature;

use App\Actions\Branch\BranchActions;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\ActionsTestCase;

class BranchActionsEditTest extends ActionsTestCase
{
    use WithFaker;

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

        $branchArr = Branch::factory()->make()->toArray();

        $result = $this->branchActions->update($branch, $branchArr);

        $this->assertInstanceOf(Branch::class, $result);
        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'company_id' => $branch->company_id,
            'code' => $branchArr['code'],
            'name' => $branchArr['name'],
        ]);
    }

    public function test_branch_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Branch::factory()->setStatusActive()->setIsMainBranch()
                ))->create();

        $branch = $user->companies()->inRandomOrder()->first()
                    ->branches()->inRandomOrder()->first();

        $branchArr = [];

        $this->branchActions->update($branch, $branchArr);
    }
}
