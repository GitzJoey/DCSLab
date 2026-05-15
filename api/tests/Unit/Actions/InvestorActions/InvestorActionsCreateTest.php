<?php

namespace Tests\Unit\Actions\InvestorActions;

use App\Actions\Investor\InvestorActions;
use App\Models\Company;
use App\Models\Investor;
use App\Models\User;
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

        $payload = Investor::factory()->for($company)
            ->make()->toArray();

        $dto = new \App\DTOs\InvestorCreateDTO(
            companyId: $payload['company_id'],
            code: $payload['code'],
            name: $payload['name'],
            remarks: $payload['remarks']
        );

        $result = $this->investorActions->create($dto);
        $this->assertDatabaseHas('investors', [
            'id' => $result->id,
            'company_id' => $payload['company_id'],
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_investor_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $dto = new \App\DTOs\InvestorCreateDTO();

        $this->investorActions->create($dto);
    }
}
