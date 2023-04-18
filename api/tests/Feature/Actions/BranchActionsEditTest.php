<?php

namespace Tests\Feature;

use App\Actions\Branch\BranchActions;
use App\Models\Branch;
use App\Models\User;
use Database\Seeders\BranchTableSeeder;
use Database\Seeders\CompanyTableSeeder;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BranchActionsEditTest extends TestCase
{
    use WithFaker;

    private $branchActions;

    private $companySeeder;

    private $branchSeeder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branchActions = app(BranchActions::class);

        $this->companySeeder = new CompanyTableSeeder();
        $this->branchSeeder = new BranchTableSeeder();
    }

    public function test_branch_actions_call_update_expect_db_updated()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->branchSeeder->callWith(BranchTableSeeder::class, [3, $companyId]);

        $branch = $user->companies->first()->branches->first();
        $branchArr = Branch::factory()->make();

        $result = $this->branchActions->update($branch, $branchArr->toArray());

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

        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->branchSeeder->callWith(BranchTableSeeder::class, [3, $companyId]);

        $branch = $user->companies->first()->branches->first();
        $branchArr = [];

        $this->branchActions->update($branch, $branchArr);
    }
}
