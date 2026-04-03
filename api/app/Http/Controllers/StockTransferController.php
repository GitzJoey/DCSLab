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
            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:1'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:1'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockTransferActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,

                search: $validatedRequest['search'] ?? null,
                startDate: $validatedRequest['start_date'] ?? null,
                endDate: $validatedRequest['end_date'] ?? null,
                sourceWarehouseId: $validatedRequest['source_warehouse_id'] ?? null,
                destinationWarehouseId: $validatedRequest['destination_warehouse_id'] ?? null,

                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: (function () use ($validatedRequest) {
                        $pagination = null;
                        if (isset($validatedRequest['paginate'])) {
                            $pagination = new ExecutePaginationDTO(
                                page: $validatedRequest['paginate']['page'],
                                perPage: $validatedRequest['paginate']['per_page']
                            );
                        }

                        return $pagination;
                    })(),
                    get: (function () use ($validatedRequest) {
                        $get = null;
                        if (isset($validatedRequest['get'])) {
                            $get = new ExecuteGetDTO(
                                limit: $validatedRequest['get']['limit']
                            );
                        }

                        return $get;
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
                    items: $validatedRequest['items'],
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
                    deleteItemIds: $validatedRequest['delete_item_ids'],
                    items: $validatedRequest['items'],
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
