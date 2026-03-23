<?php

namespace App\Http\Controllers;

use App\Actions\StockTransferProductUnit\StockTransferProductUnitActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\StockTransferProductUnitCreateDTO;
use App\DTOs\StockTransferProductUnitUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\StockTransferProductUnit\StockTransferProductUnitStoreRequest;
use App\Http\Requests\StockTransferProductUnit\StockTransferProductUnitUpdateRequest;
use App\Http\Resources\StockTransferProductUnitResource;
use App\Models\StockTransferProductUnit;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockTransferProductUnitController extends BaseController
{
    private $stockTransferProductUnitActions;

    public function __construct(StockTransferProductUnitActions $stockTransferProductUnitActions)
    {
        parent::__construct();

        $this->stockTransferProductUnitActions = $stockTransferProductUnitActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', StockTransferProductUnit::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'stock_transfer_source_warehouse_id' => $request->filled('stock_transfer_source_warehouse_id') ? HashidsHelper::decodeId($request->stock_transfer_source_warehouse_id) : null,
            'stock_transfer_destination_warehouse_id' => $request->filled('stock_transfer_destination_warehouse_id') ? HashidsHelper::decodeId($request->stock_transfer_destination_warehouse_id) : null,
            'product_unit_product_category_id' => $request->filled('product_unit_product_category_id') ? HashidsHelper::decodeId($request->product_unit_product_category_id) : null,
            'product_unit_product_brand_id' => $request->filled('product_unit_product_brand_id') ? HashidsHelper::decodeId($request->product_unit_product_brand_id) : null,
        ]);

        $validated = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'stock_transfer_code' => ['nullable', 'string'],
            'stock_transfer_start_date' => ['nullable', 'date'],
            'stock_transfer_end_date' => ['nullable', 'date', 'after_or_equal:stock_transfer_start_date'],
            'stock_transfer_source_warehouse_id' => ['nullable', 'integer', new ExistsForCompany('warehouses', $request->company_id)],
            'stock_transfer_destination_warehouse_id' => ['nullable', 'integer', new ExistsForCompany('warehouses', $request->company_id)],
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
            $result = $this->stockTransferProductUnitActions->readAny(
                withTrashed: $validated['with_trashed'],
                companyId: $validated['company_id'],
                branchId: $validated['branch_id'] ?? null,
                search: $validated['search'] ?? null,

                stockTransferCode: $validated['stock_transfer_code'] ?? null,
                stockTransferStartDate: $validated['stock_transfer_start_date'] ?? null,
                stockTransferEndDate: $validated['stock_transfer_end_date'] ?? null,
                stockTransferSourceWarehouseId: $validated['stock_transfer_source_warehouse_id'] ?? null,
                stockTransferDestinationWarehouseId: $validated['stock_transfer_destination_warehouse_id'] ?? null,
                productUnitCode: $validated['product_unit_code'] ?? null,
                productUnitProductName: $validated['product_unit_product_name'] ?? null,
                productUnitProductCategoryId: $validated['product_unit_product_category_id'] ?? null,
                productUnitProductBrandId: $validated['product_unit_product_brand_id'] ?? null,

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
                ),
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = StockTransferProductUnitResource::collection($result);

            return $response;
        }
    }

    public function read(StockTransferProductUnit $stockTransferProductUnit)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $stockTransferProductUnit);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferProductUnitActions->read($stockTransferProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new StockTransferProductUnitResource($result);

            return $response;
        }
    }

    public function store(StockTransferProductUnitStoreRequest $request)
    {
        $validated = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $data = new StockTransferProductUnitCreateDTO(
                companyId: $validated['company_id'],
                branchId: $validated['branch_id'],
                stockTransferId: $validated['stock_transfer_id'],
                qty: $validated['qty'],
                productUnitId: $validated['product_unit_id'],
                productUnitConversionValue: $validated['product_unit_conversion_value'],
                remarks: $validated['remarks'],
                serials: $validated['serials'],
            );
            $result = $this->stockTransferProductUnitActions->create($data);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(StockTransferProductUnit $stockTransferProductUnit, StockTransferProductUnitUpdateRequest $request)
    {
        $validated = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $data = new StockTransferProductUnitUpdateDTO(
                qty: $validated['qty'],
                productUnitId: $validated['product_unit_id'],
                productUnitConversionValue: $validated['product_unit_conversion_value'],
                remarks: $validated['remarks'],
                deleteSerialIds: $validated['delete_serial_ids'],
                serials: $validated['serials'],
            );
            $result = $this->stockTransferProductUnitActions->update(
                stockTransferProductUnit: $stockTransferProductUnit,
                data: $data
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(StockTransferProductUnit $stockTransferProductUnit)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $stockTransferProductUnit);

        $result = false;
        $errorMsg = '';

        try {
            $result = $this->stockTransferProductUnitActions->delete($stockTransferProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
