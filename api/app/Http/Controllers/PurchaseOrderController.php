<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseOrder\PurchaseOrderActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\PurchaseOrderCreateDTO;
use App\DTOs\PurchaseOrderUpdateDTO;
use App\Enums\PurchaseProgressStatusEnum;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseOrder\PurchaseOrderStoreRequest;
use App\Http\Requests\PurchaseOrder\PurchaseOrderUpdateRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use App\Rules\IsValidSupplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends BaseController
{
    private PurchaseOrderActions $purchaseOrderActions;

    public function __construct(PurchaseOrderActions $purchaseOrderActions)
    {
        parent::__construct();

        $this->purchaseOrderActions = $purchaseOrderActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PurchaseOrder::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'supplier_id' => $request->filled('supplier_id') ? HashidsHelper::decodeId($request->supplier_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],

            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'start_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'end_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'supplier_id' => ['nullable', 'integer', new IsValidSupplier($request->company_id)],
            'progress_status' => ['nullable', 'string', Rule::in(PurchaseProgressStatusEnum::toArrayValue())],

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
            $result = $this->purchaseOrderActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                startDate: $validatedRequest['start_date'] ?? null,
                endDate: $validatedRequest['end_date'] ?? null,
                supplierId: $validatedRequest['supplier_id'] ?? null,
                progressStatus: $validatedRequest['progress_status'] ?? null,

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
                ),
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return PurchaseOrderResource::collection($result);
        }
    }

    public function read(PurchaseOrder $purchaseOrder)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $purchaseOrder);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderActions->read($purchaseOrder);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return new PurchaseOrderResource($result);
        }
    }

    public function getProgressStatuses()
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PurchaseOrder::class);

        return $this->purchaseOrderActions->getProgressStatuses();
    }

    public function store(PurchaseOrderStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->purchaseOrderActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();

            $dto = new PurchaseOrderCreateDTO(
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'],
                code: $validatedRequest['code'],
                date: $validatedRequest['date'],
                dueDays: $validatedRequest['due_days'],
                supplierId: $validatedRequest['supplier_id'],
                remarks: $validatedRequest['remarks'],
                rounding: (float) $validatedRequest['rounding'],
                globalDiscounts: $validatedRequest['global_discounts'],
                items: $validatedRequest['items'],
                downPayments: $validatedRequest['down_payments'],
                refundedDownPayments: $validatedRequest['refunded_down_payments'],
            );
            $result = $this->purchaseOrderActions->create(
                data: $dto
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PurchaseOrder $purchaseOrder, PurchaseOrderUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->purchaseOrderActions->isUniqueCode(
                    $purchaseOrder->company_id,
                    $validatedRequest['code'],
                    $purchaseOrder->id,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();

            $result = $this->purchaseOrderActions->update(
                purchaseOrder: $purchaseOrder,
                data: new PurchaseOrderUpdateDTO(
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    dueDays: $validatedRequest['due_days'],
                    supplierId: $validatedRequest['supplier_id'],
                    remarks: $validatedRequest['remarks'],
                    rounding: (float) $validatedRequest['rounding'],
                    deleteGlobalDiscountIds: $validatedRequest['delete_global_discount_ids'],
                    globalDiscounts: $validatedRequest['global_discounts'],
                    deleteItemIds: $validatedRequest['delete_item_ids'],
                    items: $validatedRequest['items'],
                    deleteDownPaymentIds: $validatedRequest['delete_down_payment_ids'],
                    downPayments: $validatedRequest['down_payments'],
                    deleteRefundedDownPaymentIds: $validatedRequest['delete_refunded_down_payment_ids'],
                    refundedDownPayments: $validatedRequest['refunded_down_payments'],
                ),
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseOrder $purchaseOrder)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $purchaseOrder);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->purchaseOrderActions->delete($purchaseOrder);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
