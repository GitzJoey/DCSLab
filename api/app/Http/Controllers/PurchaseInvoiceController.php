<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseInvoice\PurchaseInvoiceActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\PurchaseInvoiceCreateDTO;
use App\DTOs\PurchaseInvoiceUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseInvoice\PurchaseInvoiceStoreRequest;
use App\Http\Requests\PurchaseInvoice\PurchaseInvoiceUpdateRequest;
use App\Http\Resources\PurchaseInvoiceResource;
use App\Models\PurchaseInvoice;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use App\Rules\IsValidSupplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends BaseController
{
    public function __construct(
        private readonly PurchaseInvoiceActions $purchaseInvoiceActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PurchaseInvoice::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'supplier_id' => $request->filled('supplier_id') ? HashidsHelper::decodeId($request->supplier_id) : null,
            'purchase_order_id' => $request->filled('purchase_order_id') ? HashidsHelper::decodeId($request->purchase_order_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'start_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'end_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'supplier_id' => ['nullable', 'integer', new IsValidSupplier($request->company_id)],
            'purchase_order_id' => ['nullable', 'integer', new ExistsForCompany('purchase_orders', $request->company_id)],
            'is_posted' => ['nullable', 'boolean'],
            'is_paid_off' => ['nullable', 'boolean'],

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
            $result = $this->purchaseInvoiceActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,
                startDate: $validatedRequest['start_date'] ?? null,
                endDate: $validatedRequest['end_date'] ?? null,
                supplierId: $validatedRequest['supplier_id'] ?? null,
                purchaseOrderId: $validatedRequest['purchase_order_id'] ?? null,
                isPosted: isset($validatedRequest['is_posted']) ? (bool) $validatedRequest['is_posted'] : null,
                isPaidOff: isset($validatedRequest['is_paid_off']) ? (bool) $validatedRequest['is_paid_off'] : null,
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
            : PurchaseInvoiceResource::collection($result);
    }

    public function read(PurchaseInvoice $purchaseInvoice)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $purchaseInvoice);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseInvoiceActions->read($purchaseInvoice);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result)
            ? response()->error($errorMsg)
            : new PurchaseInvoiceResource($result);
    }

    public function store(PurchaseInvoiceStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->purchaseInvoiceActions->isUniqueCode(
                    companyId: $validatedRequest['company_id'],
                    code: $validatedRequest['code'],
                    exceptId: null,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();

            $dto = new PurchaseInvoiceCreateDTO(
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
                globalDiscount: (float) $validatedRequest['global_discount'],
                rounding: (float) $validatedRequest['rounding'],
                items: $validatedRequest['items'],
                payments: $validatedRequest['payments'],
            );
            $result = $this->purchaseInvoiceActions->create($dto);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PurchaseInvoice $purchaseInvoice, PurchaseInvoiceUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->purchaseInvoiceActions->isUniqueCode(
                    companyId: $purchaseInvoice->company_id,
                    code: $validatedRequest['code'],
                    exceptId: $purchaseInvoice->id,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();

            $result = $this->purchaseInvoiceActions->update(
                purchaseInvoice: $purchaseInvoice,
                data: new PurchaseInvoiceUpdateDTO(
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
                    globalDiscount: (float) $validatedRequest['global_discount'],
                    rounding: (float) $validatedRequest['rounding'],
                    deleteItemIds: $validatedRequest['delete_item_ids'],
                    items: $validatedRequest['items'],
                    deletePaymentIds: $validatedRequest['delete_payment_ids'],
                    payments: $validatedRequest['payments'],
                ),
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseInvoice $purchaseInvoice)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $purchaseInvoice);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseInvoiceActions->delete($purchaseInvoice);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
