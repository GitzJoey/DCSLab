<?php

namespace Tests\Feature;

use App\Actions\ProductGroup\ProductGroupActions;
use App\Models\Company;
use App\Models\ProductGroup;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductGroupActionsEditTest extends TestCase
{
    use WithFaker;

    private $productGroupActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productGroupActions = app(ProductGroupActions::class);
    }

    public function test_product_group_actions_call_update_expect_db_updated()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault()
                        ->has(ProductGroup::factory(), 'productGroups'), 'companies')
                    ->create();

        $productGroup = $user->companies->first()->productGroups->first();
        $productGroupArr = ProductGroup::factory()->make();

        $result = $this->productGroupActions->update($productGroup, $productGroupArr->toArray());

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
                    ->has(Company::factory()->setIsDefault()
                        ->has(ProductGroup::factory(), 'product_groups'), 'companies')
                    ->create();

        $productGroup = $user->companies->first()->product_groups->first();
        $productGroupArr = [];

        $this->productGroupActions->update($productGroup, $productGroupArr);
    }
}
