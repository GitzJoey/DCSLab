<?php

namespace Tests\Unit\Actions\InvestorActions;

use App\Actions\Investor\InvestorActions;
use App\Models\Company;
use App\Models\Investor;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class InvestorActionsCreateTest extends ActionsTestCase
{
    private InvestorActions $investorActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->investorActions = new InvestorActions();
    }

    public function test_investor_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $investorArr = Investor::factory()->for($company)
            ->make()->toArray();

        $result = $this->investorActions->create($investorArr);

        $this->assertDatabaseHas('investors', [
            'id' => $result->id,
            'company_id' => $investorArr['company_id'],
            'code' => $investorArr['code'],
            'name' => $investorArr['name'],
        ]);
    }

    public function test_investor_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->investorActions->create([]);
    }
}
