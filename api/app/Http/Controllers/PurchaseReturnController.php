<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseReturn\PurchaseReturnActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\PurchaseReturnCreateDTO;
use App\DTOs\PurchaseReturnUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseReturn\PurchaseReturnStoreRequest;
use App\Http\Requests\PurchaseReturn\PurchaseReturnUpdateRequest;
use App\Http\Resources\PurchaseReturnResource;
use App\Models\PurchaseReturn;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use App\Rules\IsValidSupplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends BaseController
{
    public function __construct(
        private readonly PurchaseReturnActions $purchaseReturnActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) {
            return response()->error(trans('auth.unauthenticated'), 401);
        }
        $this->authorize('viewAny', PurchaseReturn::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'supplier_id' => $request->filled('supplier_id') ? HashidsHelper::decodeId($request->supplier_id) : null,
            'purchase_invoice_id' => $request->filled('purchase_invoice_id') ? HashidsHelper::decodeId($request->purchase_invoice_id) : null,
            'warehouse_id' => $request->filled('warehouse_id') ? HashidsHelper::decodeId($request->warehouse_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'start_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'end_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'supplier_id' => ['nullable', 'integer', new IsValidSupplier($request->company_id)],
            'purchase_invoice_id' => ['nullable', 'integer', new ExistsForCompany('purchase_invoices', $request->company_id)],
            'warehouse_id' => ['nullable', 'integer', new ExistsForCompany('warehouses', $request->company_id)],
            'is_settled' => ['nullable', 'boolean'],

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
            $result = $this->purchaseReturnActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,
                startDate: $validatedRequest['start_date'] ?? null,
                endDate: $validatedRequest['end_date'] ?? null,
                supplierId: $validatedRequest['supplier_id'] ?? null,
                purchaseInvoiceId: $validatedRequest['purchase_invoice_id'] ?? null,
                warehouseId: $validatedRequest['warehouse_id'] ?? null,
                isSettled: isset($validatedRequest['is_settled']) ? (bool) $validatedRequest['is_settled'] : null,
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
            : PurchaseReturnResource::collection($result);
    }

    public function read(PurchaseReturn $purchaseReturn)
    {
        if (! Auth::check()) {
            return response()->error(trans('auth.unauthenticated'), 401);
        }
        $this->authorize('view', $purchaseReturn);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnActions->read($purchaseReturn);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result)
            ? response()->error($errorMsg)
            : new PurchaseReturnResource($result);
    }

    public function store(PurchaseReturnStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->purchaseReturnActions->isUniqueCode(
                    companyId: $validatedRequest['company_id'],
                    code: $validatedRequest['code'],
                    exceptId: null,
                );
                if (! $isUniqueCode) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            DB::beginTransaction();

            $dto = new PurchaseReturnCreateDTO(
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'],
                code: $validatedRequest['code'],
                date: $validatedRequest['date'],
                supplierId: $validatedRequest['supplier_id'],
                purchaseInvoiceId: $validatedRequest['purchase_invoice_id'],
                warehouseId: $validatedRequest['warehouse_id'],
                globalDiscount: (float) $validatedRequest['global_discount'],
                rounding: (float) $validatedRequest['rounding'],
                remarks: $validatedRequest['remarks'],
                isPosted: $validatedRequest['is_posted'],
                items: $validatedRequest['items'],
                refunds: $validatedRequest['refunds'],
            );
            $result = $this->purchaseReturnActions->create($dto);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PurchaseReturn $purchaseReturn, PurchaseReturnUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->purchaseReturnActions->isUniqueCode(
                    companyId: $purchaseReturn->company_id,
                    code: $validatedRequest['code'],
                    exceptId: $purchaseReturn->id,
                );
                if (! $isUniqueCode) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            DB::beginTransaction();

            $result = $this->purchaseReturnActions->update(
                purchaseReturn: $purchaseReturn,
                data: new PurchaseReturnUpdateDTO(
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    supplierId: $validatedRequest['supplier_id'],
                    purchaseInvoiceId: $validatedRequest['purchase_invoice_id'],
                    warehouseId: $validatedRequest['warehouse_id'],
                    globalDiscount: (float) $validatedRequest['global_discount'],
                    rounding: (float) $validatedRequest['rounding'],
                    remarks: $validatedRequest['remarks'],
                    isPosted: $validatedRequest['is_posted'],
                    deleteItemIds: $validatedRequest['delete_item_ids'],
                    items: $validatedRequest['items'],
                    deleteRefundIds: $validatedRequest['delete_refund_ids'],
                    refunds: $validatedRequest['refunds'],
                ),
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseReturn $purchaseReturn)
    {
        if (! Auth::check()) {
            return response()->error(trans('auth.unauthenticated'), 401);
        }
        $this->authorize('delete', $purchaseReturn);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseReturnActions->delete($purchaseReturn);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
