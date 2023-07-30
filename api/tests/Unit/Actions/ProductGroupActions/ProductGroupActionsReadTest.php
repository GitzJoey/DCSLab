<?php

namespace Tests\Unit\Actions\ProductGroupActions;

use App\Actions\ProductGroup\ProductGroupActions;
use App\Enums\ProductGroupCategory;
use App\Models\Company;
use App\Models\ProductGroup;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class ProductGroupActionsReadTest extends ActionsTestCase
{
    private ProductGroupActions $productGroupActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->productGroupActions = new ProductGroupActions();
    }

    public function test_product_group_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->productGroupActions->readAny(
            companyId: $company->id,
            category: ProductGroup::factory()->make()->category->value,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_product_group_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->productGroupActions->readAny(
            companyId: $maxId,
            category: ProductGroup::factory()->make()->category->value,
            search: '',
            paginate: false
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_product_group_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $productGroupCount = 5;
        $idxTest = random_int(0, $productGroupCount - 1);
        $defaultName = ProductGroup::factory()->make()->name;
        $testName = ProductGroup::factory()->insertStringInName('testing')->make()->name;

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory()->count($productGroupCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'name' => $sequence->index == $idxTest ? $testName : $defaultName,
                        ]
                    ))
                ))
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->productGroupActions->readAny(
            companyId: $company->id,
            category: null,
            search: 'testing',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 1);
    }

    public function test_product_group_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->productGroupActions->readAny(
            companyId: $company->id,
            category: null,
            search: '',
            paginate: true,
            page: -1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() > 1);
    }

    public function test_product_group_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->productGroupActions->readAny(
            companyId: $company->id,
            category: null,
            search: '',
            paginate: true,
            page: 1,
            perPage: -10
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 5);
    }

    public function test_product_group_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory()->count(5))
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->productGroupActions->readAny(
            companyId: $company->id,
            category: ProductGroup::factory()->make()->category->value,
            search: '',
            paginate: true,
            page: 1,
            perPage: 10
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_product_group_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory()->count(5))
            )->create();

        $productGroup = $user->companies()->inRandomOrder()->first()
            ->productGroups()->inRandomOrder()->first();

        $result = $this->productGroupActions->read($productGroup);

        $this->assertInstanceOf(ProductGroup::class, $result);
    }

    public function test_product_group_actions_call_get_product_group_ddl_expect_collection()
    {
        $productCount = random_int(1, 5);
        $serviceCount = random_int(1, 5);

        $productGroupCategory = ProductGroupCategory::toArrayValue();
        $category = fake()->randomElement($productGroupCategory);

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(ProductGroup::factory()->count($productCount)->setCategoryToProduct())
                ->has(ProductGroup::factory()->count($serviceCount)->setCategoryToService())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->productGroupActions->getProductGroupDDL(
            companyId: $company->id,
            category: $category
        );

        $this->assertInstanceOf(Collection::class, $result);

        foreach ($result as $productGroup) {
            $this->assertTrue($productGroup->category->value == $category);
        }

        if ($category == ProductGroupCategory::PRODUCTS->value) {
            $this->assertTrue($result->count() == $productCount);
        }

        if ($category == ProductGroupCategory::SERVICES->value) {
            $this->assertTrue($result->count() == $serviceCount);
        }

        $result = $this->productGroupActions->getProductGroupDDL(
            companyId: $company->id,
            category: null
        );

        $this->assertInstanceOf(Collection::class, $result);

        $this->assertTrue($result->count() == $productCount + $serviceCount);
    }
}
