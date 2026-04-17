<?php

namespace App\Http\Controllers;

use App\Actions\StockAdjustment\StockAdjustmentActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\StockAdjustmentCreateDTO;
use App\DTOs\StockAdjustmentUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\StockAdjustment\StockAdjustmentStoreRequest;
use App\Http\Requests\StockAdjustment\StockAdjustmentUpdateRequest;
use App\Http\Resources\StockAdjustmentResource;
use App\Models\StockAdjustment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends BaseController
{
    private $stockAdjustmentActions;

    public function __construct(StockAdjustmentActions $stockAdjustmentActions)
    {
        parent::__construct();

        $this->stockAdjustmentActions = $stockAdjustmentActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', StockAdjustment::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'category_id' => $request->filled('category_id') ? HashidsHelper::decodeId($request->category_id) : null,
            'in_warehouse_id' => $request->filled('in_warehouse_id') ? HashidsHelper::decodeId($request->in_warehouse_id) : null,
            'out_warehouse_id' => $request->filled('out_warehouse_id') ? HashidsHelper::decodeId($request->out_warehouse_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'category_id' => ['nullable', 'integer', new ExistsForCompany('stock_adjustment_categories', $request->company_id)],
            'in_warehouse_id' => ['nullable', 'integer', new ExistsForCompany('warehouses', $request->company_id)],
            'out_warehouse_id' => ['nullable', 'integer', new ExistsForCompany('warehouses', $request->company_id)],

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
            $result = $this->stockAdjustmentActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                startDate: $validatedRequest['start_date'] ?? null,
                endDate: $validatedRequest['end_date'] ?? null,
                categoryId: $validatedRequest['category_id'] ?? null,
                inWarehouseId: $validatedRequest['in_warehouse_id'] ?? null,
                outWarehouseId: $validatedRequest['out_warehouse_id'] ?? null,

                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: (function () use ($validatedRequest) {
                        $pagination = null;
                        if (isset($validatedRequest['paginate'])) {
                            $pagination = new ExecutePaginationDTO(
                                page: $validatedRequest['paginate']['page'],
                                perPage: $validatedRequest['paginate']['per_page'],
                            );
                        }

                        return $pagination;
                    })(),
                    get: (function () use ($validatedRequest) {
                        $get = null;
                        if (isset($validatedRequest['get'])) {
                            $get = new ExecuteGetDTO(
                                limit: $validatedRequest['get']['limit'],
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
        } else {
            return StockAdjustmentResource::collection($result);
        }
    }

    public function read(StockAdjustment $stockAdjustment)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $stockAdjustment);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->stockAdjustmentActions->read($stockAdjustment);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return new StockAdjustmentResource($result);
        }
    }

    public function store(StockAdjustmentStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->stockAdjustmentActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();

            $data = new StockAdjustmentCreateDTO(
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'],
                code: $validatedRequest['code'],
                date: $validatedRequest['date'],
                categoryId: $validatedRequest['category_id'],
                inWarehouseId: $validatedRequest['in_warehouse_id'] ?? null,
                outWarehouseId: $validatedRequest['out_warehouse_id'] ?? null,
                remarks: $validatedRequest['remarks'] ?? null,
                isPosted: $validatedRequest['is_posted'],
                inItems: $validatedRequest['in_items'] ?? [],
                outItems: $validatedRequest['out_items'] ?? [],
            );

            $result = $this->stockAdjustmentActions->create($data);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(StockAdjustment $stockAdjustment, StockAdjustmentUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->stockAdjustmentActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    $stockAdjustment->id,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();

            $result = $this->stockAdjustmentActions->update(
                stockAdjustment: $stockAdjustment,
                data: new StockAdjustmentUpdateDTO(
                    companyId: $validatedRequest['company_id'],
                    branchId: $validatedRequest['branch_id'],
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    categoryId: $validatedRequest['category_id'],
                    inWarehouseId: $validatedRequest['in_warehouse_id'] ?? null,
                    outWarehouseId: $validatedRequest['out_warehouse_id'] ?? null,
                    remarks: $validatedRequest['remarks'] ?? null,
                    isPosted: $validatedRequest['is_posted'],
                    deleteInItemIds: $validatedRequest['delete_in_item_ids'] ?? [],
                    inItems: $validatedRequest['in_items'] ?? [],
                    deleteOutItemIds: $validatedRequest['delete_out_item_ids'] ?? [],
                    outItems: $validatedRequest['out_items'] ?? [],
                )
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(StockAdjustment $stockAdjustment)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $stockAdjustment);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->stockAdjustmentActions->delete($stockAdjustment);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
