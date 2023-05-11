<?php

namespace Tests\Unit;

use App\Actions\Branch\BranchActions;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;

use Tests\ActionsTestCase;

class BranchActionsDeleteTest extends ActionsTestCase
{
    private BranchActions $branchActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branchActions = new BranchActions();
    }

    public function test_branch_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
            )->create();

        $branch = $user->companies()->inRandomOrder()->first()
            ->branches()->inRandomOrder()->first();

        $result = $this->branchActions->delete($branch);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('branches', [
            'id' => $branch->id,
        ]);
    }
}
