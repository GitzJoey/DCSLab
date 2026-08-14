<?php

namespace Tests\Unit\Actions\CompanyActions;

use App\Actions\Company\CompanyActions;
use App\DTOs\CompanyCreateDTO;
use App\Models\Company;
use App\Models\User;
use Tests\ActionsTestCase;

class CompanyActionsCreateTest extends ActionsTestCase
{
    private CompanyActions $companyActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyActions = app(CompanyActions::class);
    }

    public function test_company_action_call_create_expect_db_has_record()
    {
        $user = User::factory()->create();

        $payload = Company::factory()
            ->setStatusActive()->setIsDefault()->make([
                'user_id' => $user->id,
            ])->toArray();

        $dto = new CompanyCreateDTO(
            code: $payload['code'],
            name: $payload['name'],
            address: $payload['address'],
            default: $payload['default'],
            status: $payload['status'],
        );

        $result = $this->companyActions->create($user, $dto);

        $this->assertDatabaseHas('companies', [
            'id' => $result->id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_company_action_call_create_with_default_true_expect_previous_default_reset()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $previousDefaultCompany = $user->companies()->first();
        $payload = Company::factory()
            ->setStatusActive()
            ->setIsDefault()
            ->make()
            ->toArray();

        $dto = new CompanyCreateDTO(
            code: $payload['code'],
            name: $payload['name'],
            address: $payload['address'],
            default: true,
            status: $payload['status'],
        );

        $result = $this->companyActions->create($user, $dto);
        $this->assertTrue((bool) $result->default);
        $this->assertDatabaseHas('companies', [
            'id' => $previousDefaultCompany->id,
            'default' => false,
        ]);
    }

    public function test_company_service_call_create_with_empty_array_parameters_expect_exception()
    {
        $user = User::factory()->create();
        $dtoClass = CompanyCreateDTO::class;

        $this->expectException(\ArgumentCountError::class);
        $dto = new $dtoClass(...[]);

        $this->companyActions->create($user, $dto);
    }
}
