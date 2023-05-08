<?php

namespace Tests\Feature;

use App\Actions\Branch\BranchActions;
use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BranchActionsCreateTest extends TestCase
{
    use WithFaker;

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

        $branchArr = Branch::factory()->for($company)
                        ->setStatusActive()->setIsMainBranch()
                        ->make()->toArray();

        $result = $this->branchActions->create($branchArr);

        $this->assertDatabaseHas('branches', [
            'id' => $result->id,
            'company_id' => $branchArr['company_id'],
            'code' => $branchArr['code'],
            'name' => $branchArr['name'],
        ]);
    }

    public function test_branch_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->branchActions->create([]);
    }
}
