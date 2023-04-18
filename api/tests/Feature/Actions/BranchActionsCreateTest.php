<?php

namespace Tests\Feature;

use App\Actions\Branch\BranchActions;
use App\Actions\ChartOfAccount\ChartOfAccountActions;
use App\Models\Branch;
use App\Models\User;
use Database\Seeders\BranchTableSeeder;
use Database\Seeders\CompanyTableSeeder;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BranchActionsCreateTest extends TestCase
{
    use WithFaker;

    private $branchActions;

    private $chartOfAccountActions;

    private $companySeeder;

    private $branchSeeder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branchActions = app(BranchActions::class);
        $this->chartOfAccountActions = app(ChartOfAccountActions::class);

        $this->companySeeder = new CompanyTableSeeder();
        $this->branchSeeder = new BranchTableSeeder();
    }

    public function test_branch_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->chartOfAccountActions->createDefaultAccountPerCompany($companyId);

        $branchArr = Branch::factory()->make([
            'company_id' => $companyId,
        ])->toArray();

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
