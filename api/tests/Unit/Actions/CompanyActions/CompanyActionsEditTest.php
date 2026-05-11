<?php

namespace Tests\Unit\Actions\CompanyActions;

use App\Actions\Company\CompanyActions;
use App\DTOs\CompanyUpdateDTO;
use App\Models\Company;
use App\Models\User;
use Tests\ActionsTestCase;

class CompanyActionsEditTest extends ActionsTestCase
{
    private CompanyActions $companyActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyActions = app(CompanyActions::class);
    }

    public function test_company_service_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies->first();
        $companyArr = Company::factory()->make()->toArray();

        $result = $this->companyActions->update($user, $company, new \App\DTOs\CompanyUpdateDTO(
            code: $companyArr['code'],
            name: $companyArr['name'],
            address: $companyArr['address'],
            default: $companyArr['default'],
            status: $companyArr['status'],
        ));

        $this->assertInstanceOf(Company::class, $result);
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'code' => $companyArr['code'],
            'name' => $companyArr['name'],
        ]);
    }

    public function test_company_service_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(\ArgumentCountError::class);
        $dtoClass = CompanyUpdateDTO::class;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies->first();

        $this->companyActions->update($user, $company, new $dtoClass(...[]));
    }
}
