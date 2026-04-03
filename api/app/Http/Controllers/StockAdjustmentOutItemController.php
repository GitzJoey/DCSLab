<?php

namespace App\Http\Controllers;

use App\Actions\StockAdjustmentOutItem\StockAdjustmentOutItemActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\StockAdjustmentOutItemCreateDTO;
use App\DTOs\StockAdjustmentOutItemUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\StockAdjustmentOutItem\StockAdjustmentOutItemStoreRequest;
use App\Http\Requests\StockAdjustmentOutItem\StockAdjustmentOutItemUpdateRequest;
use App\Http\Resources\StockAdjustmentOutItemResource;
use App\Models\StockAdjustmentOutItem;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentOutItemController extends BaseController
{
    private $stockAdjustmentOutItemActions;

    public function __construct(StockAdjustmentOutItemActions $stockAdjustmentOutItemActions)
    {
        parent::__construct();
        $this->stockAdjustmentOutItemActions = $stockAdjustmentOutItemActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', StockAdjustmentOutItem::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'stock_adjustment_category_id' => $request->filled('stock_adjustment_category_id') ? HashidsHelper::decodeId($request->stock_adjustment_category_id) : null,
            'stock_adjustment_in_warehouse_id' => $request->filled('stock_adjustment_in_warehouse_id') ? HashidsHelper::decodeId($request->stock_adjustment_in_warehouse_id) : null,
            'stock_adjustment_out_warehouse_id' => $request->filled('stock_adjustment_out_warehouse_id') ? HashidsHelper::decodeId($request->stock_adjustment_out_warehouse_id) : null,
            'product_unit_product_category_id' => $request->filled('product_unit_product_category_id') ? HashidsHelper::decodeId($request->product_unit_product_category_id) : null,
            'product_unit_product_brand_id' => $request->filled('product_unit_product_brand_id') ? HashidsHelper::decodeId($request->product_unit_product_brand_id) : null,
        ]);

        $validated = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'stock_adjustment_code' => ['nullable', 'string'],
            'stock_adjustment_start_date' => ['nullable', 'date'],
            'stock_adjustment_end_date' => ['nullable', 'date', 'after_or_equal:stock_adjustment_start_date'],
            'stock_adjustment_category_id' => ['nullable', 'integer', new ExistsForCompany('stock_adjustment_categories', $request->company_id)],
            'stock_adjustment_in_warehouse_id' => ['nullable', 'integer', new ExistsForCompany('warehouses', $request->company_id)],
            'stock_adjustment_out_warehouse_id' => ['nullable', 'integer', new ExistsForCompany('warehouses', $request->company_id)],
            'product_unit_code' => ['nullable', 'string'],
            'product_unit_product_name' => ['nullable', 'string'],
            'product_unit_product_category_id' => ['nullable', 'integer', new ExistsForCompany('product_categories', $request->company_id)],
            'product_unit_product_brand_id' => ['nullable', 'integer', new ExistsForCompany('brands', $request->company_id)],

            'refresh' => ['required', 'boolean'],
            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:1'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:1'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockAdjustmentOutItemActions->readAny(
                withTrashed: $validated['with_trashed'],
                companyId: $validated['company_id'],
                branchId: $validated['branch_id'] ?? null,
                search: $validated['search'] ?? null,

                stockAdjustmentCode: $validated['stock_adjustment_code'] ?? null,
                stockAdjustmentStartDate: $validated['stock_adjustment_start_date'] ?? null,
                stockAdjustmentEndDate: $validated['stock_adjustment_end_date'] ?? null,
                stockAdjustmentCategoryId: $validated['stock_adjustment_category_id'] ?? null,
                stockAdjustmentInWarehouseId: $validated['stock_adjustment_in_warehouse_id'] ?? null,
                stockAdjustmentOutWarehouseId: $validated['stock_adjustment_out_warehouse_id'] ?? null,
                stockAdjustmentProductUnitCode: $validated['product_unit_code'] ?? null,
                stockAdjustmentProductUnitProductName: $validated['product_unit_product_name'] ?? null,
                stockAdjustmentProductUnitProductCategoryId: $validated['product_unit_product_category_id'] ?? null,
                stockAdjustmentProductUnitProductBrandId: $validated['product_unit_product_brand_id'] ?? null,

                execute: new ExecuteDTO(
                    useCache: ! $validated['refresh'],
                    pagination: (function () use ($validated) {
                        $pagination = null;
                        if (isset($validated['paginate'])) {
                            $pagination = new ExecutePaginationDTO(
                                page: $validated['paginate']['page'],
                                perPage: $validated['paginate']['per_page'],
                            );
                        }

                        return $pagination;
                    })(),
                    get: (function () use ($validated) {
                        $get = null;
                        if (isset($validated['get'])) {
                            $get = new ExecuteGetDTO(
                                limit: $validated['get']['limit'],
                            );
                        }

                        return $get;
                    })(),
                )
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return StockAdjustmentOutItemResource::collection($result);
    }

    public function read(StockAdjustmentOutItem $stockAdjustmentOutItem)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $stockAdjustmentOutItem);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockAdjustmentOutItemActions->read($stockAdjustmentOutItem);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new StockAdjustmentOutItemResource($result);
    }

    public function store(StockAdjustmentOutItemStoreRequest $request)
    {
        $validated = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $data = new StockAdjustmentOutItemCreateDTO(
                companyId: $validated['company_id'],
                branchId: $validated['branch_id'],
                stockAdjustmentId: $validated['stock_adjustment_id'],
                qty: $validated['qty'],
                productUnitId: $validated['product_unit_id'],
                productUnitConversionValue: $validated['product_unit_conversion_value'],
                remarks: $validated['remarks'],
                serials: $validated['serials'],
            );

            $result = $this->stockAdjustmentOutItemActions->create($data);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(StockAdjustmentOutItem $stockAdjustmentOutItem, StockAdjustmentOutItemUpdateRequest $request)
    {
        $validated = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $data = new StockAdjustmentOutItemUpdateDTO(
                qty: $validated['qty'],
                productUnitId: $validated['product_unit_id'],
                productUnitConversionValue: $validated['product_unit_conversion_value'],
                remarks: $validated['remarks'],
                deleteSerialIds: $validated['delete_serial_ids'],
                serials: $validated['serials'],
            );

            $result = $this->stockAdjustmentOutItemActions->update(
                stockAdjustmentOutItem: $stockAdjustmentOutItem,
                data: $data
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(StockAdjustmentOutItem $stockAdjustmentOutItem)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $stockAdjustmentOutItem);

        $result = false;
        $errorMsg = '';

        try {
            $result = $this->stockAdjustmentOutItemActions->delete($stockAdjustmentOutItem);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
