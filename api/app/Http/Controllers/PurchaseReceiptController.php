<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseReceipt\PurchaseReceiptActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\DTOs\PurchaseReceiptCreateDTO;
use App\DTOs\PurchaseReceiptUpdateDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseReceipt\PurchaseReceiptStoreRequest;
use App\Http\Requests\PurchaseReceipt\PurchaseReceiptUpdateRequest;
use App\Http\Resources\PurchaseReceiptResource;
use App\Models\PurchaseReceipt;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use App\Rules\IsValidDate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseReceiptController extends BaseController
{
    public function __construct(
        private readonly PurchaseReceiptActions $purchaseReceiptActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PurchaseReceipt::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'supplier_id' => $request->filled('supplier_id') ? HashidsHelper::decodeId($request->supplier_id) : null,
            'purchase_id' => $request->filled('purchase_id') ? HashidsHelper::decodeId($request->purchase_id) : null,
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
            'purchase_id' => ['nullable', 'integer', new ExistsForCompany('purchases', $request->company_id)],
            'warehouse_id' => ['nullable', 'integer', new ExistsForCompany('warehouses', $request->company_id)],
            'is_posted' => ['nullable', 'boolean'],
            'is_from_direct_purchase' => ['nullable', 'boolean'],

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
            $result = $this->purchaseReceiptActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                supplierId: $validatedRequest['supplier_id'] ?? null,
                purchaseId: $validatedRequest['purchase_id'] ?? null,
                isFromDirectPurchase: $validatedRequest['is_from_direct_purchase'] ?? null,
                startDate: $validatedRequest['start_date'] ?? null,
                endDate: $validatedRequest['end_date'] ?? null,
                warehouseId: $validatedRequest['warehouse_id'] ?? null,
                isPosted: $validatedRequest['is_posted'] ?? null,

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
            : PurchaseReceiptResource::collection($result);
    }

    public function read(PurchaseReceipt $purchaseReceipt)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $purchaseReceipt);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptActions->read($purchaseReceipt);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result)
            ? response()->error($errorMsg)
            : new PurchaseReceiptResource($result);
    }

    public function storeManual(PurchaseReceiptStoreRequest $request)
    {
        $validatedRequest = $request->validated();
        $items = collect($validatedRequest['items'])
            ->map(fn ($item) => [...$item, 'purchase_item_id' => null])
            ->all();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->purchaseReceiptActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();
            $result = $this->purchaseReceiptActions->createManual(
                data: new PurchaseReceiptCreateDTO(
                    companyId: $validatedRequest['company_id'],
                    branchId: $validatedRequest['branch_id'],
                    supplierId: $validatedRequest['supplier_id'],
                    purchaseId: $validatedRequest['purchase_id'] ?? null,
                    isFromDirectPurchase: false,
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    warehouseId: $validatedRequest['warehouse_id'],
                    remarks: $validatedRequest['remarks'] ?? null,
                    isPosted: $validatedRequest['is_posted'],
                    items: $items,
                ),
                updatePurchaseSummary: true,
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function updateManual(PurchaseReceiptUpdateRequest $request, PurchaseReceipt $purchaseReceipt)
    {
        $validatedRequest = $request->validated();
        $items = collect($validatedRequest['items'])
            ->map(fn ($item) => [...$item, 'purchase_item_id' => null])
            ->all();

        $result = null;
        $errorMsg = '';

        try {
            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUniqueCode = $this->purchaseReceiptActions->isUniqueCode(
                    $purchaseReceipt->company_id,
                    $validatedRequest['code'],
                    $purchaseReceipt->id,
                );
                if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            DB::beginTransaction();
            $result = $this->purchaseReceiptActions->updateManual(
                purchaseReceipt: $purchaseReceipt,
                data: new PurchaseReceiptUpdateDTO(
                    supplierId: $validatedRequest['supplier_id'],
                    purchaseId: $validatedRequest['purchase_id'] ?? null,
                    code: $validatedRequest['code'],
                    date: $validatedRequest['date'],
                    isFromDirectPurchase: false,
                    warehouseId: $validatedRequest['warehouse_id'],
                    remarks: $validatedRequest['remarks'] ?? null,
                    isPosted: $validatedRequest['is_posted'],

                    deleteItemIds: $validatedRequest['delete_item_ids'],
                    items: $items,
                ),
                updatePurchaseSummary: true,
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function deleteManual(PurchaseReceipt $purchaseReceipt)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $purchaseReceipt);

        $result = null;
        $errorMsg = '';

        try {
            if ($purchaseReceipt->is_from_direct_purchase) {
                return response()->error(['purchase_id' => [trans('rules.purchase_receipt.direct_mode_cannot_be_deleted_here')]], 422);
            }

            DB::beginTransaction();
            $result = $this->purchaseReceiptActions->delete($purchaseReceipt, updatePurchaseSummary: true);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
