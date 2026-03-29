<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseOrder\PurchaseOrderActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\PurchaseOrderCreateDTO;
use App\DTOs\PurchaseOrderUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseOrder\PurchaseOrderStoreRequest;
use App\Http\Requests\PurchaseOrder\PurchaseOrderUpdateRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends BaseController
{
    private $purchaseOrderActions;

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

            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'supplier_id' => ['nullable', 'integer', new ExistsForCompany('suppliers', $request->company_id)],
            'is_has_invoice' => ['nullable', 'boolean'],
            'is_received' => ['nullable', 'boolean'],
            'is_down_payment_paid_off' => ['nullable', 'boolean'],

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
                search: $validatedRequest['search'],

                startDate: $validatedRequest['start_date'] ?? null,
                endDate: $validatedRequest['end_date'] ?? null,
                supplierId: $validatedRequest['supplier_id'] ?? null,
                isHasInvoice: $validatedRequest['is_has_invoice'] ?? null,
                isReceived: $validatedRequest['is_received'] ?? null,
                isDownPaymentPaidOff: $validatedRequest['is_down_payment_paid_off'] ?? null,

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
        }

        return PurchaseOrderResource::collection($result);
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
        }

        return new PurchaseOrderResource($result);
    }

    public function store(PurchaseOrderStoreRequest $purchaseOrderRequest)
    {
        $request = $purchaseOrderRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseOrderActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    null
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $result = $this->purchaseOrderActions->create(new PurchaseOrderCreateDTO(
                companyId: $request['company_id'],
                branchId: $request['branch_id'],
                supplierId: $request['supplier_id'],
                code: $request['code'],
                date: $request['date'],
                shippingDate: $request['shipping_date'],
                shippingAddress: $request['shipping_address'],
                remarks: $request['remarks'],
                isHasInvoice: $request['is_has_invoice'],
                isReceived: $request['is_received'],
                total: $request['total'],
                globalDiscountRate: $request['global_discount_rate'],
                globalDiscountFixed: $request['global_discount_fixed'],
                grandTotal: $request['grand_total'],
                downPayment: $request['down_payment'],
                downPaymentDueDays: $request['down_payment_due_days'],
                downPaymentApplied: $request['down_payment_applied'],
                downPaymentRemaining: $request['down_payment_remaining'],
                isDownPaymentPaidOff: $request['is_down_payment_paid_off'],
                productUnits: $request['purchase_order_product_units'] ?? [],
                downPayments: $request['purchase_order_down_payments'] ?? [],
            ));

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PurchaseOrder $purchaseOrder, PurchaseOrderUpdateRequest $purchaseOrderRequest)
    {
        $request = $purchaseOrderRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->purchaseOrderActions->update(
                purchaseOrder: $purchaseOrder,
                data: new PurchaseOrderUpdateDTO(
                    companyId: $request['company_id'],
                    branchId: $request['branch_id'],
                    supplierId: $request['supplier_id'],
                    code: $request['code'],
                    date: $request['date'],
                    shippingDate: $request['shipping_date'],
                    shippingAddress: $request['shipping_address'],
                    remarks: $request['remarks'],
                    isHasInvoice: $request['is_has_invoice'],
                    isReceived: $request['is_received'],
                    total: $request['total'],
                    globalDiscountRate: $request['global_discount_rate'],
                    globalDiscountFixed: $request['global_discount_fixed'],
                    grandTotal: $request['grand_total'],
                    downPayment: $request['down_payment'],
                    downPaymentDueDays: $request['down_payment_due_days'],
                    downPaymentApplied: $request['down_payment_applied'],
                    downPaymentRemaining: $request['down_payment_remaining'],
                    isDownPaymentPaidOff: $request['is_down_payment_paid_off'],
                    deleteProductUnitIds: $request['delete_purchase_order_product_unit_ids'] ?? [],
                    productUnits: $request['purchase_order_product_units'] ?? [],
                    deleteDownPaymentIds: $request['delete_purchase_order_down_payment_ids'] ?? [],
                    downPayments: $request['purchase_order_down_payments'] ?? [],
                )
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
