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
        $payload = Company::factory()->make()->toArray();

        $dto = new CompanyUpdateDTO(
            code: $payload['code'],
            name: $payload['name'],
            address: $payload['address'],
            default: $payload['default'],
            status: $payload['status'],
        );

        $result = $this->companyActions->update($user, $company, $dto);
        $this->assertInstanceOf(Company::class, $result);
        $this->assertDatabaseHas('companies', [
            'id' => $company->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_company_service_call_update_with_default_true_expect_other_default_reset()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->has(Company::factory()->setStatusActive()->state(['default' => false]))
            ->create();

        $companies = $user->companies()->orderBy('id')->take(2)->get();
        $previousDefaultCompany = $companies[0];
        $targetCompany = $companies[1];
        $payload = Company::factory()->setStatusActive()->setIsDefault()->make()->toArray();

        $dto = new CompanyUpdateDTO(
            code: $payload['code'],
            name: $payload['name'],
            address: $payload['address'],
            default: true,
            status: $payload['status'],
        );

        $result = $this->companyActions->update($user, $targetCompany, $dto);
        $this->assertTrue((bool) $result->default);
        $this->assertDatabaseHas('companies', [
            'id' => $previousDefaultCompany->id,
            'default' => false,
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

        $dto = new $dtoClass(...[]);

        $this->companyActions->update($user, $company, $dto);
    }
}
