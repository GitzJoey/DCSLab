<?php

namespace App\Http\Controllers;

use App\Actions\SalesOrder\SalesOrderActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\SalesOrderCreateDTO;
use App\DTOs\SalesOrderUpdateDTO;
use App\Enums\ProgressStatusEnum;
use App\Helpers\HashidsHelper;
use App\Http\Requests\SalesOrder\SalesOrderStoreRequest;
use App\Http\Requests\SalesOrder\SalesOrderUpdateRequest;
use App\Http\Resources\SalesOrderResource;
use App\Models\SalesOrder;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SalesOrderController extends BaseController
{
    private SalesOrderActions $salesOrderActions;

    public function __construct(SalesOrderActions $salesOrderActions)
    {
        parent::__construct();

        $this->salesOrderActions = $salesOrderActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', SalesOrder::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'customer_id' => $request->filled('customer_id') ? HashidsHelper::decodeId($request->customer_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],

            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'start_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'end_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'customer_id' => ['nullable', 'integer', new ExistsForCompany('customers', $request->company_id)],
            'progress_status' => ['nullable', 'string', Rule::in(ProgressStatusEnum::toArrayValue())],

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
            $result = $this->salesOrderActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                startDate: $validatedRequest['start_date'] ?? null,
                endDate: $validatedRequest['end_date'] ?? null,
                customerId: $validatedRequest['customer_id'] ?? null,
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
            return SalesOrderResource::collection($result);
        }
    }

    public function read(SalesOrder $salesOrder)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $salesOrder);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->salesOrderActions->read($salesOrder);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return new SalesOrderResource($result);
        }
    }

    public function getProgressStatuses()
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', SalesOrder::class);

        return $this->salesOrderActions->getProgressStatuses();
    }

    public function store(SalesOrderStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->salesOrderActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();

            $dto = new SalesOrderCreateDTO(
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'],
                code: $validatedRequest['code'],
                date: $validatedRequest['date'],
                dueDays: $validatedRequest['due_days'],
                customerId: $validatedRequest['customer_id'],
                remarks: $validatedRequest['remarks'],
                globalDiscount: (float) $validatedRequest['global_discount'],
                rounding: (float) $validatedRequest['rounding'],
                items: $validatedRequest['items'],
                payments: $validatedRequest['payments'],
                refundedPayments: $validatedRequest['refunded_payments'],
            );
            $result = $this->salesOrderActions->create(
                data: $dto
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(SalesOrder $salesOrder, SalesOrderUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->salesOrderActions->isUniqueCode(
                    $salesOrder->company_id,
                    $validatedRequest['code'],
                    $salesOrder->id,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();

            $result = $this->salesOrderActions->update(
                salesOrder: $salesOrder,
                data: new SalesOrderUpdateDTO(
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    dueDays: $validatedRequest['due_days'],
                    customerId: $validatedRequest['customer_id'],
                    remarks: $validatedRequest['remarks'],
                    globalDiscount: (float) $validatedRequest['global_discount'],
                    rounding: (float) $validatedRequest['rounding'],
                    deleteItemIds: $validatedRequest['delete_item_ids'],
                    items: $validatedRequest['items'],
                    deletePaymentIds: $validatedRequest['delete_payment_ids'],
                    payments: $validatedRequest['payments'],
                    deleteRefundedPaymentIds: $validatedRequest['delete_refunded_payment_ids'],
                    refundedPayments: $validatedRequest['refunded_payments'],
                ),
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(SalesOrder $salesOrder)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $salesOrder);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->salesOrderActions->delete($salesOrder);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
