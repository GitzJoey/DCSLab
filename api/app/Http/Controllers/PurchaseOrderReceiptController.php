<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseOrderReceipt\PurchaseOrderReceiptActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\PurchaseOrderReceiptCreateDTO;
use App\DTOs\PurchaseOrderReceiptUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseOrderReceipt\PurchaseOrderReceiptStoreRequest;
use App\Http\Requests\PurchaseOrderReceipt\PurchaseOrderReceiptUpdateRequest;
use App\Http\Resources\PurchaseOrderReceiptResource;
use App\Models\PurchaseOrderReceipt;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderReceiptController extends BaseController
{
    public function __construct(
        private readonly PurchaseOrderReceiptActions $purchaseOrderReceiptActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) {
            return response()->error(trans('auth.unauthenticated'), 401);
        }
        $this->authorize('viewAny', PurchaseOrderReceipt::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'supplier_id' => $request->filled('supplier_id') ? HashidsHelper::decodeId($request->supplier_id) : null,
            'purchase_order_id' => $request->filled('purchase_order_id') ? HashidsHelper::decodeId($request->purchase_order_id) : null,
            'warehouse_id' => $request->filled('warehouse_id') ? HashidsHelper::decodeId($request->warehouse_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'start_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'end_date' => ['nullable', 'string', new IsValidDate('Y-m-d H:i:s')],
            'supplier_id' => ['nullable', 'integer', new ExistsForCompany('suppliers', $request->company_id)],
            'purchase_order_id' => ['nullable', 'integer', new ExistsForCompany('purchase_orders', $request->company_id)],
            'warehouse_id' => ['nullable', 'integer', new ExistsForCompany('warehouses', $request->company_id)],
            'is_posted' => ['nullable', 'boolean'],

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
            $result = $this->purchaseOrderReceiptActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                supplierId: $validatedRequest['supplier_id'] ?? null,
                purchaseOrderId: $validatedRequest['purchase_order_id'] ?? null,
                startDate: $validatedRequest['start_date'] ?? null,
                endDate: $validatedRequest['end_date'] ?? null,
                warehouseId: $validatedRequest['warehouse_id'] ?? null,
                isPosted: isset($validatedRequest['is_posted']) ? (bool) $validatedRequest['is_posted'] : null,

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
            : PurchaseOrderReceiptResource::collection($result);
    }

    public function read(PurchaseOrderReceipt $purchaseOrderReceipt)
    {
        if (! Auth::check()) {
            return response()->error(trans('auth.unauthenticated'), 401);
        }
        $this->authorize('view', $purchaseOrderReceipt);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderReceiptActions->read($purchaseOrderReceipt);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result)
            ? response()->error($errorMsg)
            : new PurchaseOrderReceiptResource($result);
    }

    public function store(PurchaseOrderReceiptStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->purchaseOrderReceiptActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null,
                );
                if (! $isUniqueCode) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            DB::beginTransaction();
            $dto = new PurchaseOrderReceiptCreateDTO(
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'],
                code: $validatedRequest['code'],
                date: $validatedRequest['date'],
                supplierId: $validatedRequest['supplier_id'],
                purchaseOrderId: $validatedRequest['purchase_order_id'],
                warehouseId: $validatedRequest['warehouse_id'],
                remarks: $validatedRequest['remarks'] ?? null,
                isPosted: $validatedRequest['is_posted'],
                items: $validatedRequest['items'],
                costs: $validatedRequest['costs'],
            );
            $result = $this->purchaseOrderReceiptActions->create($dto);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PurchaseOrderReceipt $purchaseOrderReceipt, PurchaseOrderReceiptUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->purchaseOrderReceiptActions->isUniqueCode(
                    $purchaseOrderReceipt->company_id,
                    $validatedRequest['code'],
                    $purchaseOrderReceipt->id,
                );
                if (! $isUniqueCode) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            DB::beginTransaction();
            $result = $this->purchaseOrderReceiptActions->update(
                purchaseOrderReceipt: $purchaseOrderReceipt,
                data: new PurchaseOrderReceiptUpdateDTO(
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    supplierId: $validatedRequest['supplier_id'],
                    purchaseOrderId: $validatedRequest['purchase_order_id'],
                    warehouseId: $validatedRequest['warehouse_id'],
                    remarks: $validatedRequest['remarks'] ?? null,
                    isPosted: $validatedRequest['is_posted'],
                    deleteItemIds: $validatedRequest['delete_item_ids'],
                    items: $validatedRequest['items'],
                    deleteCostIds: $validatedRequest['delete_cost_ids'],
                    costs: $validatedRequest['costs'],
                ),
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseOrderReceipt $purchaseOrderReceipt)
    {
        if (! Auth::check()) {
            return response()->error(trans('auth.unauthenticated'), 401);
        }
        $this->authorize('delete', $purchaseOrderReceipt);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseOrderReceiptActions->delete($purchaseOrderReceipt);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
