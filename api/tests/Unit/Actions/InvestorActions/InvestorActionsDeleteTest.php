<?php

namespace Tests\Unit\Actions\InvestorActions;

use App\Actions\Investor\InvestorActions;
use App\Models\Company;
use App\Models\Investor;
use App\Models\User;
use Tests\ActionsTestCase;

class InvestorActionsDeleteTest extends ActionsTestCase
{
    private InvestorActions $investorActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->investorActions = new InvestorActions();
    }

    public function test_investor_actions_call_delete_expect_bool()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Investor::factory())
            )->create();

        $investor = $user->companies()->inRandomOrder()->first()
            ->investors()->inRandomOrder()->first();
        $result = $this->investorActions->delete($investor);

        $this->assertIsBool($result);
        $this->assertTrue($result);
        $this->assertSoftDeleted('investors', [
            'id' => $investor->id,
        ]);
    }
}
