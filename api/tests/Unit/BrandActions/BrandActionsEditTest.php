<?php

namespace Tests\Unit\BrandActions;

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
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Brand::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $brand = $company->brands()->inRandomOrder()->first();

        $brandArr = Brand::factory()->make()->toArray();

        $result = $this->brandActions->update($brand, $brandArr);

        $this->assertInstanceOf(Brand::class, $result);
        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'company_id' => $brand->company_id,
            'code' => $brandArr['code'],
            'name' => $brandArr['name'],
        ]);
    }

    public function test_brand_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(Brand::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $brand = $company->brands()->inRandomOrder()->first();

        $brandArr = Brand::factory()->make()->toArray();
        $brandArr = [];

        $this->brandActions->update($brand, $brandArr);
    }
}
