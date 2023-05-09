<?php

namespace Tests\Unit;

use App\Actions\ProductGroup\ProductGroupActions;
use App\Models\Company;
use App\Models\ProductGroup;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\ActionsTestCase;

class ProductGroupActionsEditTest extends ActionsTestCase
{
    use WithFaker;

    private ProductGroupActions $productGroupActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productGroupActions = new ProductGroupActions();
    }

    public function test_product_group_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->count(5))
            )->create();

        $company = $user->companies()->first();

        $productGroup = $company->productGroups()->inRandomOrder()->first();

        $productGroupArr = ProductGroup::factory()->for($company)->make()->toArray();

        $result = $this->productGroupActions->update($productGroup, $productGroupArr);

        $this->assertInstanceOf(ProductGroup::class, $result);
        $this->assertDatabaseHas('product_groups', [
            'id' => $productGroup->id,
            'company_id' => $productGroup->company_id,
            'code' => $productGroupArr['code'],
            'name' => $productGroupArr['name'],
        ]);
    }

    public function test_product_group_actions_call_update_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);

        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory())
            )->create();

        $productGroup = $user->companies()->inRandomOrder()->first()
            ->productGroups()->inRandomOrder()->first();

        $productGroupArr = [];

        $this->productGroupActions->update($productGroup, $productGroupArr);
    }
}
