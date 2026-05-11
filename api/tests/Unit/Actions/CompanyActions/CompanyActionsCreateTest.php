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

        $companyArr = Company::factory()
            ->setStatusActive()->setIsDefault()->make([
                'user_id' => $user->id,
            ])->toArray();

        $result = $this->companyActions->create($user, new \App\DTOs\CompanyCreateDTO(
            code: $companyArr['code'],
            name: $companyArr['name'],
            address: $companyArr['address'],
            default: $companyArr['default'],
            status: $companyArr['status'],
        ));

        $this->assertDatabaseHas('companies', [
            'id' => $result->id,
            'code' => $companyArr['code'],
            'name' => $companyArr['name'],
        ]);
    }

    public function test_company_service_call_create_with_empty_array_parameters_expect_exception()
    {
        $user = User::factory()->create();
        $dtoClass = CompanyCreateDTO::class;

        $this->expectException(\ArgumentCountError::class);
        $this->companyActions->create($user, new $dtoClass(...[]));
    }
}
