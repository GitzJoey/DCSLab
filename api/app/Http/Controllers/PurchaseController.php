<?php

namespace App\Http\Controllers;

use App\Actions\Purchase\PurchaseActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\Purchase\PurchaseStoreRequest;
use App\Http\Requests\Purchase\PurchaseUpdateRequest;
use App\Http\Resources\PurchaseResource;
use App\Models\Purchase;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidWarehouse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends BaseController
{
    private $purchaseActions;

    public function __construct(PurchaseActions $purchaseActions)
    {
        parent::__construct();

        $this->purchaseActions = $purchaseActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', Purchase::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'warehouse_id' => $request->filled('warehouse_id') ? HashidsHelper::decodeId($request->warehouse_id) : null,
            'supplier_id' => $request->filled('supplier_id') ? HashidsHelper::decodeId($request->supplier_id) : null,
            'purchase_order_id' => $request->filled('purchase_order_id') ? HashidsHelper::decodeId($request->purchase_order_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'warehouse_id' => ['nullable', 'integer', new IsValidWarehouse($request->company_id, false)],
            'supplier_id' => ['nullable', 'integer', new ExistsForCompany('suppliers', $request->company_id)],
            'purchase_order_id' => ['nullable', 'integer', new ExistsForCompany('purchase_orders', $request->company_id)],
            'is_posted' => ['nullable', 'boolean'],
            'is_paid_off' => ['nullable', 'boolean'],
            'is_valid' => ['nullable', 'boolean'],

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
            $result = $this->purchaseActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                startDate: $validatedRequest['start_date'] ?? null,
                endDate: $validatedRequest['end_date'] ?? null,
                warehouseId: $validatedRequest['warehouse_id'] ?? null,
                supplierId: $validatedRequest['supplier_id'] ?? null,
                purchaseOrderId: $validatedRequest['purchase_order_id'] ?? null,
                isPosted: $validatedRequest['is_posted'] ?? null,
                isPaidOff: $validatedRequest['is_paid_off'] ?? null,
                isValid: $validatedRequest['is_valid'] ?? null,

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
        }

        return PurchaseResource::collection($result);
    }

    public function read(Purchase $purchase)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $purchase);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseActions->read($purchase);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new PurchaseResource($result);
    }

    public function store(PurchaseStoreRequest $purchaseRequest)
    {
        $request = $purchaseRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    null
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $result = $this->purchaseActions->create($request);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(Purchase $purchase, PurchaseUpdateRequest $purchaseRequest)
    {
        $request = $purchaseRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    $purchase->id
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $result = $this->purchaseActions->update(
                purchase: $purchase,
                data: $request
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Purchase $purchase)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $purchase);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->purchaseActions->delete($purchase);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
