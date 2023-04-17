<?php

namespace Tests\Feature;

use App\Actions\ProductGroup\ProductGroupActions;
use App\Models\Company;
use App\Models\ProductGroup;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductGroupActionsCreateTest extends TestCase
{
    use WithFaker;

    private $productGroupActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productGroupActions = app(ProductGroupActions::class);
    }

    public function test_product_group_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
                    ->has(Company::factory()->setIsDefault(), 'companies')
                    ->create();

        $productGroupArr = ProductGroup::factory()->make([
            'company_id' => $user->companies->first()->id,
        ]);

        $result = $this->productGroupActions->create($productGroupArr->toArray());

        $this->assertDatabaseHas('product_groups', [
            'id' => $result->id,
            'company_id' => $productGroupArr['company_id'],
            'code' => $productGroupArr['code'],
            'name' => $productGroupArr['name'],
            'category' => $productGroupArr['category'],
        ]);
    }

    public function test_product_group_actions_call_create_with_empty_array_parameters_expect_exception()
    {
        $this->expectException(Exception::class);
        $this->productGroupActions->create([]);
    }
}
