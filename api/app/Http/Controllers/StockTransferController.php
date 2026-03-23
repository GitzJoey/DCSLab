<?php

namespace App\Http\Controllers;

use App\Actions\StockTransfer\StockTransferActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\StockTransferCreateDTO;
use App\DTOs\StockTransferUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\StockTransfer\StockTransferStoreRequest;
use App\Http\Requests\StockTransfer\StockTransferUpdateRequest;
use App\Http\Resources\StockTransferResource;
use App\Models\StockTransfer;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidWarehouse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockTransferController extends BaseController
{
    private $stockTransferActions;

    public function __construct(StockTransferActions $stockTransferActions)
    {
        parent::__construct();

        $this->stockTransferActions = $stockTransferActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', StockTransfer::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'source_warehouse_id' => $request->filled('source_warehouse_id') ? HashidsHelper::decodeId($request->source_warehouse_id) : null,
            'destination_warehouse_id' => $request->filled('destination_warehouse_id') ? HashidsHelper::decodeId($request->destination_warehouse_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'source_warehouse_id' => ['nullable', 'integer', new IsValidWarehouse($request->company_id, false)],
            'destination_warehouse_id' => ['nullable', 'integer', new IsValidWarehouse($request->company_id, false)],

            'refresh' => ['required', 'boolean'],
            'paginate' => ['required', 'array'],
            'paginate.page' => ['required_with:paginate', 'nullable', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'nullable', 'integer', 'min:1'],
            'get' => ['required', 'array'],
            'get.limit' => ['required_with:get', 'nullable', 'integer', 'min:1'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'],

                search: $validatedRequest['search'],
                startDate: $validatedRequest['start_date'],
                endDate: $validatedRequest['end_date'],
                sourceWarehouseId: $validatedRequest['source_warehouse_id'],
                destinationWarehouseId: $validatedRequest['destination_warehouse_id'],

                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: (function () use ($validatedRequest) {
                        if (! is_null($validatedRequest['paginate']['page']) && ! is_null($validatedRequest['paginate']['per_page'])) {
                            return new ExecutePaginationDTO(
                                page: $validatedRequest['paginate']['page'],
                                perPage: $validatedRequest['paginate']['per_page']
                            );
                        }

                        return null;
                    })(),
                    get: (function () use ($validatedRequest) {
                        if (! is_null($validatedRequest['get']['limit'])) {
                            return new ExecuteGetDTO(
                                limit: $validatedRequest['get']['limit']
                            );
                        }

                        return null;
                    })()
                )
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return StockTransferResource::collection($result);
    }

    public function read(StockTransfer $stockTransfer)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $stockTransfer);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferActions->read($stockTransfer);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new StockTransferResource($result);
    }

    public function store(StockTransferStoreRequest $stockTransferStoreRequest)
    {
        $validatedRequest = $stockTransferStoreRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->stockTransferActions->isUniqueCode(
                    companyId: $validatedRequest['company_id'],
                    code: $validatedRequest['code'],
                    exceptId: null
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $result = $this->stockTransferActions->create(
                new StockTransferCreateDTO(
                    companyId: $validatedRequest['company_id'],
                    branchId: $validatedRequest['branch_id'],
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    sourceWarehouseId: $validatedRequest['source_warehouse_id'],
                    destinationWarehouseId: $validatedRequest['destination_warehouse_id'],
                    remarks: $validatedRequest['remarks'],
                    isPosted: $validatedRequest['is_posted'],
                    productUnits: $validatedRequest['product_units'],
                )
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(StockTransfer $stockTransfer, StockTransferUpdateRequest $stockTransferUpdateRequest)
    {
        $validatedRequest = $stockTransferUpdateRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->stockTransferActions->isUniqueCode(
                    companyId: $validatedRequest['company_id'],
                    code: $validatedRequest['code'],
                    exceptId: $stockTransfer->id
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $result = $this->stockTransferActions->update(
                stockTransfer: $stockTransfer,
                data: new StockTransferUpdateDTO(
                    companyId: $validatedRequest['company_id'],
                    branchId: $validatedRequest['branch_id'],
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    sourceWarehouseId: $validatedRequest['source_warehouse_id'],
                    destinationWarehouseId: $validatedRequest['destination_warehouse_id'],
                    remarks: $validatedRequest['remarks'],
                    isPosted: $validatedRequest['is_posted'],
                    deleteProductUnitIds: $validatedRequest['delete_product_unit_ids'],
                    productUnits: $validatedRequest['product_units'],
                )
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(StockTransfer $stockTransfer)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $stockTransfer);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->stockTransferActions->delete($stockTransfer);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
