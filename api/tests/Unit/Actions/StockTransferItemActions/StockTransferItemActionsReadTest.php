<?php

namespace Tests\Unit\Actions\StockTransferItemActions;

use App\Actions\StockTransferItem\StockTransferItemActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Models\Company;
use App\Models\StockTransferItem;
use App\Models\User;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Tests\ActionsTestCase;

class StockTransferItemActionsReadTest extends ActionsTestCase
{
    private StockTransferItemActions $stockTransferItemActions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockTransferItemActions = app(StockTransferItemActions::class);
    }

    public function test_stock_transfer_item_actions_call_read_any_with_paginate_true_expect_paginator_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransferItem::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->stockTransferItemActions->readAny(
            withTrashed: false,
            companyId: $company->id,
            branchId: null,
            search: '',

            stockTransferCode: null,
            stockTransferStartDate: null,
            stockTransferEndDate: null,
            stockTransferSourceWarehouseId: null,
            stockTransferDestinationWarehouseId: null,
            productUnitCode: null,
            productUnitProductName: null,
            productUnitProductCategoryId: null,
            productUnitProductBrandId: null,
            execute: new ExecuteDTO(
                useCache: true,
                pagination: new ExecutePaginationDTO(page: 1, perPage: 10),
                get: null,
            )
        );

        $this->assertInstanceOf(Paginator::class, $result);
    }

    public function test_stock_transfer_item_actions_call_read_any_with_paginate_false_expect_collection_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransferItem::factory())
            )->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->stockTransferItemActions->readAny(
            withTrashed: false,
            companyId: $company->id,
            branchId: null,
            search: '',

            stockTransferCode: null,
            stockTransferStartDate: null,
            stockTransferEndDate: null,
            stockTransferSourceWarehouseId: null,
            stockTransferDestinationWarehouseId: null,
            productUnitCode: null,
            productUnitProductName: null,
            productUnitProductCategoryId: null,
            productUnitProductBrandId: null,
            execute: new ExecuteDTO(
                useCache: true,
                pagination: null,
                get: new ExecuteGetDTO(limit: 10),
            )
        );

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function test_stock_transfer_item_actions_call_read_any_with_nonexistance_companyId_expect_empty_collection()
    {
        $maxId = Company::max('id') + 1;

        $result = $this->stockTransferItemActions->readAny(
            withTrashed: false,
            companyId: $maxId,
            branchId: null,
            search: '',

            stockTransferCode: null,
            stockTransferStartDate: null,
            stockTransferEndDate: null,
            stockTransferSourceWarehouseId: null,
            stockTransferDestinationWarehouseId: null,
            productUnitCode: null,
            productUnitProductName: null,
            productUnitProductCategoryId: null,
            productUnitProductBrandId: null,
            execute: new ExecuteDTO(
                useCache: true,
                pagination: null,
                get: new ExecuteGetDTO(limit: 10),
            )
        );

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertEmpty($result);
    }

    public function test_stock_transfer_item_actions_call_read_any_with_search_parameter_expect_filtered_results()
    {
        $stockTransferItemCount = 4;
        $idxTest = random_int(0, $stockTransferItemCount - 1);
        $defaultRemarks = 'default remarks';
        $testRemarks = 'testing remarks';

        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransferItem::factory()->count($stockTransferItemCount)
                    ->state(new Sequence(
                        fn (Sequence $sequence) => [
                            'remarks' => $sequence->index == $idxTest ? $testRemarks : $defaultRemarks,
                        ]
                    ))
                )
            )
            ->create();

        $company = $user->companies()->inRandomOrder()->first();

        $result = $this->stockTransferItemActions->readAny(
            withTrashed: false,
            companyId: $company->id,
            branchId: null,
            search: 'testing',

            stockTransferCode: null,
            stockTransferStartDate: null,
            stockTransferEndDate: null,
            stockTransferSourceWarehouseId: null,
            stockTransferDestinationWarehouseId: null,
            productUnitCode: null,
            productUnitProductName: null,
            productUnitProductCategoryId: null,
            productUnitProductBrandId: null,
            execute: new ExecuteDTO(
                useCache: true,
                pagination: new ExecutePaginationDTO(page: 1, perPage: 10),
                get: null,
            )
        );

        $this->assertInstanceOf(Paginator::class, $result);
        $this->assertTrue($result->total() == 1);
    }

    public function test_stock_transfer_item_actions_call_read_any_with_page_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_stock_transfer_item_actions_call_read_any_with_perpage_parameter_negative_expect_results()
    {
        $this->markTestIncomplete('Need to implement test');
    }

    public function test_stock_transfer_item_actions_call_read_expect_object()
    {
        $user = User::factory()
            ->has(Company::factory()->setStatusActive()->setIsDefault()
                ->has(StockTransferItem::factory())
            )->create();

        $stockTransferItem = $user->companies()->inRandomOrder()->first()
            ->stockTransferItems()->inRandomOrder()->first();

        $result = $this->stockTransferItemActions->read($stockTransferItem);

        $this->assertInstanceOf(StockTransferItem::class, $result);
    }
}
