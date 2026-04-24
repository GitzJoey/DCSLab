<?php

namespace App\Http\Controllers;

use App\Actions\Purchase\PurchaseActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\PurchaseDirectCreateDTO;
use App\DTOs\PurchaseDirectUpdateDTO;
use App\DTOs\PurchaseManualCreateDTO;
use App\DTOs\PurchaseManualUpdateDTO;
use App\Enums\PurchaseReceiptModeEnum;
use App\Helpers\HashidsHelper;
use App\Http\Requests\Purchase\PurchaseDirectStoreRequest;
use App\Http\Requests\Purchase\PurchaseDirectUpdateRequest;
use App\Http\Requests\Purchase\PurchaseManualStoreRequest;
use App\Http\Requests\Purchase\PurchaseManualUpdateRequest;
use App\Http\Resources\PurchaseResource;
use App\Models\Purchase;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use App\Rules\IsValidSupplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends BaseController
{
    public function __construct(
        private readonly PurchaseActions $purchaseActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', Purchase::class);

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
                supplierId: $validatedRequest['supplier_id'] ?? null,
                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: isset($validatedRequest['paginate'])
                        ? new ExecutePaginationDTO(
                            page: $validatedRequest['paginate']['page'],
                            perPage: $validatedRequest['paginate']['per_page'],
                        )
                        : null,
                    get: isset($validatedRequest['get'])
                        ? new ExecuteGetDTO(limit: $validatedRequest['get']['limit'])
                        : null,
                ),
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result)
            ? response()->error($errorMsg)
            : PurchaseResource::collection($result);
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

        return is_null($result)
            ? response()->error($errorMsg)
            : new PurchaseResource($result);
    }

    public function storeDirect(PurchaseDirectStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->purchaseActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();
            $result = $this->purchaseActions->createDirect(
                data: new PurchaseDirectCreateDTO(
                    companyId: $validatedRequest['company_id'],
                    branchId: $validatedRequest['branch_id'],
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    dueDays: $validatedRequest['due_days'],
                    supplierId: $validatedRequest['supplier_id'],
                    purchaseOrderId: $validatedRequest['purchase_order_id'],
                    directReceiptWarehouseId: $validatedRequest['direct_receipt_warehouse_id'],
                    taxInvoiceNumber: $validatedRequest['tax_invoice_number'],
                    taxInvoiceVatBase: (float) $validatedRequest['tax_invoice_vat_base'],
                    taxInvoiceVat: (float) $validatedRequest['tax_invoice_vat'],
                    remarks: $validatedRequest['remarks'],
                    isPosted: $validatedRequest['is_posted'],
                    additionalCost: (float) $validatedRequest['additional_cost'],
                    rounding: (float) $validatedRequest['rounding'],

                    items: $validatedRequest['items'],

                    globalDiscounts: $validatedRequest['global_discounts'],

                    additionalCosts: $validatedRequest['additional_costs'],
                )
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function storeManual(PurchaseManualStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->purchaseActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();

            $result = $this->purchaseActions->createManual(
                data: new PurchaseManualCreateDTO(
                    companyId: $validatedRequest['company_id'],
                    branchId: $validatedRequest['branch_id'],
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    dueDays: $validatedRequest['due_days'],
                    supplierId: $validatedRequest['supplier_id'],
                    purchaseOrderId: $validatedRequest['purchase_order_id'],
                    taxInvoiceNumber: $validatedRequest['tax_invoice_number'],
                    taxInvoiceVatBase: (float) $validatedRequest['tax_invoice_vat_base'],
                    taxInvoiceVat: (float) $validatedRequest['tax_invoice_vat'],
                    remarks: $validatedRequest['remarks'],
                    isPosted: $validatedRequest['is_posted'],
                    additionalCost: (float) $validatedRequest['additional_cost'],
                    rounding: (float) $validatedRequest['rounding'],

                    items: $validatedRequest['items'],

                    globalDiscounts: $validatedRequest['global_discounts'],

                    additionalCosts: $validatedRequest['additional_costs'],

                    manualReceipts: $validatedRequest['manual_receipts'],
                )
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function updateDirect(Purchase $purchase, PurchaseDirectUpdateRequest $request)
    {
        if ($purchase->receipt_mode !== PurchaseReceiptModeEnum::DIRECT) {
            return response()->error([
                'receipt_mode' => ['Purchase ini bukan mode direct.'],
            ], 422);
        }

        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->purchaseActions->isUniqueCode(
                    $purchase->company_id,
                    $validatedRequest['code'],
                    $purchase->id,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();

            $result = $this->purchaseActions->updateDirect(
                purchase: $purchase,
                data: new PurchaseDirectUpdateDTO(
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    dueDays: $validatedRequest['due_days'],
                    supplierId: $validatedRequest['supplier_id'],
                    purchaseOrderId: $validatedRequest['purchase_order_id'],
                    directReceiptWarehouseId: $validatedRequest['direct_receipt_warehouse_id'],
                    taxInvoiceNumber: $validatedRequest['tax_invoice_number'],
                    taxInvoiceVatBase: (float) $validatedRequest['tax_invoice_vat_base'],
                    taxInvoiceVat: (float) $validatedRequest['tax_invoice_vat'],
                    remarks: $validatedRequest['remarks'],
                    isPosted: $validatedRequest['is_posted'],
                    additionalCost: (float) $validatedRequest['additional_cost'],
                    rounding: (float) $validatedRequest['rounding'],

                    deleteItemIds: $validatedRequest['delete_item_ids'],
                    items: $validatedRequest['items'],

                    deleteGlobalDiscountIds: $validatedRequest['delete_global_discount_ids'],
                    globalDiscounts: $validatedRequest['global_discounts'],

                    deleteAdditionalCostIds: $validatedRequest['delete_additional_cost_ids'],
                    additionalCosts: $validatedRequest['additional_costs'],
                ),
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function updateManual(Purchase $purchase, PurchaseManualUpdateRequest $request)
    {
        if ($purchase->receipt_mode !== PurchaseReceiptModeEnum::MANUAL) {
            return response()->error([
                'receipt_mode' => ['Purchase ini bukan mode manual.'],
            ], 422);
        }

        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->purchaseActions->isUniqueCode(
                    $purchase->company_id,
                    $validatedRequest['code'],
                    $purchase->id,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();
            $result = $this->purchaseActions->updateManual(
                purchase: $purchase,
                data: new PurchaseManualUpdateDTO(
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    dueDays: $validatedRequest['due_days'],
                    supplierId: $validatedRequest['supplier_id'],
                    purchaseOrderId: $validatedRequest['purchase_order_id'],
                    taxInvoiceNumber: $validatedRequest['tax_invoice_number'],
                    taxInvoiceVatBase: (float) $validatedRequest['tax_invoice_vat_base'],
                    taxInvoiceVat: (float) $validatedRequest['tax_invoice_vat'],
                    remarks: $validatedRequest['remarks'],
                    isPosted: $validatedRequest['is_posted'],
                    additionalCost: (float) $validatedRequest['additional_cost'],
                    rounding: (float) $validatedRequest['rounding'],

                    deleteItemIds: $validatedRequest['delete_item_ids'],
                    items: $validatedRequest['items'],

                    deleteGlobalDiscountIds: $validatedRequest['delete_global_discount_ids'],
                    globalDiscounts: $validatedRequest['global_discounts'],

                    deleteAdditionalCostIds: $validatedRequest['delete_additional_cost_ids'],
                    additionalCosts: $validatedRequest['additional_costs'],

                    manualReceipts: $validatedRequest['manual_receipts'],
                ),
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
