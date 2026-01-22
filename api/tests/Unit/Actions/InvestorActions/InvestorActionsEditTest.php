<?php

namespace Tests\Unit\Actions\InvestorActions;

use App\Actions\Investor\InvestorActions;
use App\Models\Company;
use App\Models\Investor;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class InvestorActionsEditTest extends ActionsTestCase
{
    private InvestorActions $investorActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->investorActions = new InvestorActions();
    }

    public function test_investor_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Investor::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $investor = $company->investors()->inRandomOrder()->first();

        $investorArr = Investor::factory()->make()->toArray();

        $result = $this->investorActions->update($investor, $investorArr);

        $this->assertInstanceOf(Investor::class, $result);
        $this->assertDatabaseHas('investors', [
            'id' => $investor->id,
            'company_id' => $investor->company_id,
            'code' => $investorArr['code'],
            'name' => $investorArr['name'],
        ]);
    }

    public function test_investor_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Investor::factory())
            )->create();

        $investor = $user->companies()->inRandomOrder()->first()
            ->investors()->inRandomOrder()->first();

        $investorArr = [];

        $this->investorActions->update($investor, $investorArr);
    }
}
