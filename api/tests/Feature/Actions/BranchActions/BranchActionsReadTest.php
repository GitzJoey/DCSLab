<?php

namespace Tests\Feature;

use App\Actions\Branch\BranchActions;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BranchActionsReadTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branchActions = new BranchActions();
    }

    public function test_branch_actions_call_read_expect_object()
    {
        $user = User::factory()
                ->has(Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Branch::factory()->setStatusActive()->setIsMainBranch())
                )->create();

        $branch = $user->companies()->inRandomOrder()->first()
                    ->branches()->inRandomOrder()->first();

        $result = $this->branchActions->read($branch);

        $this->assertInstanceOf(Branch::class, $result);
    }
}
