<?php

namespace App\Http\Controllers;

use App\Actions\StockTransferProductUnitSerial\StockTransferProductUnitSerialActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\StockTransferProductUnitSerialCreateDTO;
use App\DTOs\StockTransferProductUnitSerialUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\StockTransferProductUnitSerial\StockTransferProductUnitSerialStoreRequest;
use App\Http\Requests\StockTransferProductUnitSerial\StockTransferProductUnitSerialUpdateRequest;
use App\Http\Resources\StockTransferProductUnitSerialResource;
use App\Models\StockTransferProductUnitSerial;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockTransferProductUnitSerialController extends BaseController
{
    private $stockTransferProductUnitSerialActions;

    public function __construct(StockTransferProductUnitSerialActions $stockTransferProductUnitSerialActions)
    {
        parent::__construct();

        $this->stockTransferProductUnitSerialActions = $stockTransferProductUnitSerialActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', StockTransferProductUnitSerial::class);

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
            $result = $this->stockTransferProductUnitSerialActions->readAny(
                withTrashed: $validated['with_trashed'],
                companyId: $validated['company_id'],
                branchId: $validated['branch_id'] ?? null,
                search: $validated['search'] ?? null,

                stockTransferCode: $validated['stock_transfer_code'] ?? null,
                stockTransferStartDate: $validated['stock_transfer_start_date'] ?? null,
                stockTransferEndDate: $validated['stock_transfer_end_date'] ?? null,
                stockTransferSourceWarehouseId: $validated['stock_transfer_source_warehouse_id'] ?? null,
                stockTransferDestinationWarehouseId: $validated['stock_transfer_destination_warehouse_id'] ?? null,
                stockTransferProductUnitCode: $validated['product_unit_code'] ?? null,
                stockTransferProductUnitProductName: $validated['product_unit_product_name'] ?? null,
                stockTransferProductUnitProductCategoryId: $validated['product_unit_product_category_id'] ?? null,
                stockTransferProductUnitProductBrandId: $validated['product_unit_product_brand_id'] ?? null,

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
            $response = StockTransferProductUnitSerialResource::collection($result);

            return $response;
        }
    }

    public function read(StockTransferProductUnitSerial $stockTransferProductUnitSerial)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $stockTransferProductUnitSerial);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferProductUnitSerialActions->read($stockTransferProductUnitSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new StockTransferProductUnitSerialResource($result);

            return $response;
        }
    }

    public function store(StockTransferProductUnitSerialStoreRequest $request)
    {
        $validated = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $data = new StockTransferProductUnitSerialCreateDTO(
                companyId: $validated['company_id'],
                branchId: $validated['branch_id'],
                stockTransferId: $validated['stock_transfer_id'],
                stockTransferProductUnitId: $validated['stock_transfer_product_unit_id'],
                serial: $validated['serial'],
            );
            $result = $this->stockTransferProductUnitSerialActions->create($data);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(StockTransferProductUnitSerial $stockTransferProductUnitSerial, StockTransferProductUnitSerialUpdateRequest $request)
    {
        $validated = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $data = StockTransferProductUnitSerialUpdateDTO::fromStockTransferProductUnitSerial(
                $stockTransferProductUnitSerial,
                $validated['serial'],
            );
            $result = $this->stockTransferProductUnitSerialActions->update(
                stockTransferProductUnitSerial: $stockTransferProductUnitSerial,
                data: $data
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(StockTransferProductUnitSerial $stockTransferProductUnitSerial)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $stockTransferProductUnitSerial);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->stockTransferProductUnitSerialActions->delete($stockTransferProductUnitSerial);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
