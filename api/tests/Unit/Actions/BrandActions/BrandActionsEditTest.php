<?php

namespace Tests\Unit\Actions\BrandActions;

use App\Actions\Brand\BrandActions;
use App\Models\Brand;
use App\Models\Company;
use App\Models\User;
use Exception;
use Tests\ActionsTestCase;

class BrandActionsEditTest extends ActionsTestCase
{
    private BrandActions $brandActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->brandActions = new BrandActions();
    }

    public function test_brand_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Brand::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();
        $brand = $company->brands()->inRandomOrder()->first();

        $payload = Brand::factory()->make()->toArray();

        $dto = new \App\DTOs\BrandUpdateDTO(
            code: $payload['code'],
            name: $payload['name']
        );

        $result = $this->brandActions->update($brand, $dto);
        $this->assertInstanceOf(Brand::class, $result);
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'company_id' => $brand->company_id,
            'code' => $payload['code'],
            'name' => $payload['name'],
        ]);
    }

    public function test_brand_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(Brand::factory())
            )->create();

        $brand = $user->companies()->inRandomOrder()->first()
            ->brands()->inRandomOrder()->first();

        $payload = [];

        $dto = new \App\DTOs\BrandUpdateDTO();

        $this->brandActions->update($brand, $dto);
    }
}
