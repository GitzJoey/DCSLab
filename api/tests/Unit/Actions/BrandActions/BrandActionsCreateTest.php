<?php

namespace Tests\Unit\Actions\BrandActions;

use App\Actions\Brand\BrandActions;
use App\Models\Brand;
use App\Models\Company;
use App\Models\User;
use Tests\ActionsTestCase;

class BrandActionsCreateTest extends ActionsTestCase
{
    private BrandActions $brandActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brandActions = new BrandActions();
    }

    public function test_brand_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault())
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $payload = Brand::factory()->for($company)->make()->toArray();

        $dto = new \App\DTOs\BrandCreateDTO(
            companyId: $payload['company_id'],
            code: $payload['code'],
            name: $payload['name']
        );

        $result = $this->brandActions->create($dto);
        $this->assertDatabaseHas('brands', [
            'id' => $result->id,
            'company_id' => $payload['company_id'],
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_brand_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $dto = new \App\DTOs\BrandCreateDTO();

        $this->brandActions->create($dto);
    }
}
