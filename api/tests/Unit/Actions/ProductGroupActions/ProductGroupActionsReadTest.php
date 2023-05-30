<?php

namespace Tests\Unit\Actions\ProductGroupActions;

use App\Actions\ProductGroup\ProductGroupActions;
use App\Models\Company;
use App\Models\ProductGroup;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
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
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
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
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->count(5))
                    ->has(ProductGroup::factory()->insertStringInName('testing')->count(5))
            )->create();

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
        $this->assertTrue($result->total() == 5);
    }

    public function test_product_group_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $user = User::factory()
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
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
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
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
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
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
            ->has(
                Company::factory()->setStatusActive()->setIsDefault()
                    ->has(ProductGroup::factory()->count(5))
            )->create();

        $productGroup = $user->companies()->inRandomOrder()->first()
            ->productGroups()->inRandomOrder()->first();

        $result = $this->productGroupActions->read($productGroup);

        $this->assertInstanceOf(ProductGroup::class, $result);
    }
}
