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
    
    private ProductGroupActions $productGroupActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productGroupActions = new ProductGroupActions();
    }

    public function test_product_group_actions_call_create_expect_db_has_record()
    {
        $user = User::factory()
                    ->has(Company::factory()->setStatusActive()->setIsDefault()
                    )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $productGroupArr = ProductGroup::factory()->for($company)->make()->toArray();

        $result = $this->productGroupActions->create($productGroupArr);

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
