<?php

namespace Tests\Feature;

use App\Actions\Branch\BranchActions;
use App\Models\Branch;
use App\Models\User;
use Database\Seeders\BranchTableSeeder;
use Database\Seeders\CompanyTableSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BranchActionsReadTest extends TestCase
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

    public function test_branch_actions_call_read_expect_object()
    {
        $user = User::factory()->create();

        $this->companySeeder->callWith(CompanyTableSeeder::class, [1, $user->id]);
        $company = $user->companies->first();
        $companyId = $company->id;

        $this->branchSeeder->callWith(BranchTableSeeder::class, [3, $companyId]);

        $branch = $user->companies->first()->branches()->inRandomOrder()->first();

        $result = $this->branchActions->read($branch);

        $this->assertInstanceOf(Branch::class, $result);
    }
}
