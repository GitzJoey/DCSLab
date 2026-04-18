<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseOrderDownPayment\PurchaseOrderDownPaymentActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Enums\PurchaseOrderDownPaymentAllocationStatusEnum;
use App\Helpers\HashidsHelper;
use App\Http\Resources\PurchaseOrderDownPaymentResource;
use App\Models\PurchaseOrderDownPayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class PurchaseOrderDownPaymentController extends BaseController
{
    public function __construct(
        private PurchaseOrderDownPaymentActions $purchaseOrderDownPaymentActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PurchaseOrderDownPayment::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'purchase_order_id' => $request->filled('purchase_order_id') ? HashidsHelper::decodeId($request->purchase_order_id) : null,
            'supplier_id' => $request->filled('supplier_id') ? HashidsHelper::decodeId($request->supplier_id) : null,
            'cash_account_id' => $request->filled('cash_account_id') ? HashidsHelper::decodeId($request->cash_account_id) : null,
        ]);

        $validated = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'purchase_order_id' => ['nullable', 'integer', new ExistsForCompany('purchase_orders', $request->company_id)],
            'supplier_id' => ['nullable', 'integer', new ExistsForCompany('suppliers', $request->company_id)],
            'cash_account_id' => ['nullable', 'integer', new ExistsForCompany('cash_accounts', $request->company_id)],
            'allocation_status' => ['nullable', 'string', Rule::in(PurchaseOrderDownPaymentAllocationStatusEnum::toArrayValue())],

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
            $result = $this->purchaseOrderDownPaymentActions->readAny(
                withTrashed: $validated['with_trashed'],
                companyId: $validated['company_id'],
                branchId: $validated['branch_id'] ?? null,
                search: $validated['search'] ?? null,
                startDate: $validated['start_date'] ?? null,
                endDate: $validated['end_date'] ?? null,
                purchaseOrderId: $validated['purchase_order_id'] ?? null,
                supplierId: $validated['supplier_id'] ?? null,
                cashAccountId: $validated['cash_account_id'] ?? null,
                allocationStatus: $validated['allocation_status'] ?? null,
                execute: new ExecuteDTO(
                    useCache: ! $validated['refresh'],
                    pagination: (function () use ($validated) {
                        if (! isset($validated['paginate'])) {
                            return null;
                        }

                        return new ExecutePaginationDTO(
                            page: $validated['paginate']['page'],
                            perPage: $validated['paginate']['per_page'],
                        );
                    })(),
                    get: (function () use ($validated) {
                        if (! isset($validated['get'])) {
                            return null;
                        }

                        return new ExecuteGetDTO(
                            limit: $validated['get']['limit'],
                        );
                    })(),
                ),
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return PurchaseOrderDownPaymentResource::collection($result);
    }

    public function read(PurchaseOrderDownPayment $purchaseOrderDownPayment)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $purchaseOrderDownPayment);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderDownPaymentActions->read($purchaseOrderDownPayment);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new PurchaseOrderDownPaymentResource($result);
    }

    public function getAllocationStatuses()
    {
        return $this->purchaseOrderDownPaymentActions->getAllocationStatuses();
    }
}
