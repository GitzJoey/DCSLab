<?php

namespace Tests\Unit;

use App\Actions\Company\CompanyActions;
use App\Models\Company;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class CompanyActionsEditTest extends ActionsTestCase
{
    private CompanyActions $companyActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyActions = new CompanyActions();
    }

    public function test_company_service_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies->first();
        $companyArr = Company::factory()->make()->toArray();

        $result = $this->companyActions->update($company, $companyArr);

        $this->assertInstanceOf(Company::class, $result);
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'code' => $companyArr['code'],
            'name' => $companyArr['name'],
        ]);
    }

    public function test_company_service_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies->first();
        $companyArr = [];

        $this->companyActions->update($company, $companyArr);
    }
}
