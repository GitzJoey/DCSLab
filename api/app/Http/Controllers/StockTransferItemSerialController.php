<?php

namespace App\Http\Controllers;

use App\Actions\StockTransferItemSerial\StockTransferItemSerialActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\StockTransferItemSerialCreateDTO;
use App\DTOs\StockTransferItemSerialUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\StockTransferItemSerial\StockTransferItemSerialStoreRequest;
use App\Http\Requests\StockTransferItemSerial\StockTransferItemSerialUpdateRequest;
use App\Http\Resources\StockTransferItemSerialResource;
use App\Models\StockTransferItemSerial;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockTransferItemSerialController extends BaseController
{
    private $stockTransferItemSerialActions;

    public function __construct(StockTransferItemSerialActions $stockTransferItemSerialActions)
    {
        parent::__construct();

        $this->stockTransferItemSerialActions = $stockTransferItemSerialActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', StockTransferItemSerial::class);

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
            $result = $this->stockTransferItemSerialActions->readAny(
                withTrashed: $validated['with_trashed'],
                companyId: $validated['company_id'],
                branchId: $validated['branch_id'] ?? null,
                search: $validated['search'] ?? null,

                stockTransferCode: $validated['stock_transfer_code'] ?? null,
                stockTransferStartDate: $validated['stock_transfer_start_date'] ?? null,
                stockTransferEndDate: $validated['stock_transfer_end_date'] ?? null,
                stockTransferSourceWarehouseId: $validated['stock_transfer_source_warehouse_id'] ?? null,
                stockTransferDestinationWarehouseId: $validated['stock_transfer_destination_warehouse_id'] ?? null,
                stockTransferItemCode: $validated['product_unit_code'] ?? null,
                stockTransferItemProductName: $validated['product_unit_product_name'] ?? null,
                stockTransferItemProductCategoryId: $validated['product_unit_product_category_id'] ?? null,
                stockTransferItemProductBrandId: $validated['product_unit_product_brand_id'] ?? null,

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
            $response = StockTransferItemSerialResource::collection($result);

            return $response;
        }
    }

    public function read(StockTransferItemSerial $stockTransferItemSerial)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $stockTransferItemSerial);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferItemSerialActions->read($stockTransferItemSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new StockTransferItemSerialResource($result);

            return $response;
        }
    }

    public function store(StockTransferItemSerialStoreRequest $request)
    {
        $validated = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $data = new StockTransferItemSerialCreateDTO(
                companyId: $validated['company_id'],
                branchId: $validated['branch_id'],
                stockTransferId: $validated['stock_transfer_id'],
                stockTransferItemId: $validated['stock_transfer_item_id'],
                serial: $validated['serial'],
            );
            $result = $this->stockTransferItemSerialActions->create($data);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(StockTransferItemSerial $stockTransferItemSerial, StockTransferItemSerialUpdateRequest $request)
    {
        $validated = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $data = StockTransferItemSerialUpdateDTO::fromStockTransferItemSerial(
                $stockTransferItemSerial,
                $validated['serial'],
            );
            $result = $this->stockTransferItemSerialActions->update(
                stockTransferItemSerial: $stockTransferItemSerial,
                data: $data
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(StockTransferItemSerial $stockTransferItemSerial)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $stockTransferItemSerial);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->stockTransferItemSerialActions->delete($stockTransferItemSerial);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
