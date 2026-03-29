<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseReceipt\PurchaseReceiptActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseReceipt\PurchaseReceiptStoreRequest;
use App\Http\Requests\PurchaseReceipt\PurchaseReceiptUpdateRequest;
use App\Http\Resources\PurchaseReceiptResource;
use App\Models\PurchaseReceipt;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseReceiptController extends BaseController
{
    private $purchaseReceiptActions;

    public function __construct(PurchaseReceiptActions $purchaseReceiptActions)
    {
        parent::__construct();

        $this->purchaseReceiptActions = $purchaseReceiptActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PurchaseReceipt::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'purchase_id' => $request->filled('purchase_id') ? HashidsHelper::decodeId($request->purchase_id) : null,
            'warehouse_id' => $request->filled('warehouse_id') ? HashidsHelper::decodeId($request->warehouse_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'purchase_id' => ['nullable', 'integer', new ExistsForCompany('purchases', $request->company_id)],
            'warehouse_id' => ['nullable', 'integer', new ExistsForCompany('warehouses', $request->company_id)],
            'is_posted' => ['nullable', 'boolean'],
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
            $result = $this->purchaseReceiptActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                purchaseId: $validatedRequest['purchase_id'] ?? null,
                warehouseId: $validatedRequest['warehouse_id'] ?? null,
                isPosted: $validatedRequest['is_posted'] ?? null,
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

        return PurchaseReceiptResource::collection($result);
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

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new PurchaseReceiptResource($result);
    }

    public function store(PurchaseReceiptStoreRequest $purchaseReceiptRequest)
    {
        $request = $purchaseReceiptRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseReceiptActions->create($request);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PurchaseReceipt $purchaseReceipt, PurchaseReceiptUpdateRequest $purchaseReceiptRequest)
    {
        $request = $purchaseReceiptRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseReceiptActions->update(
                purchaseReceipt: $purchaseReceipt,
                data: $request
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseReceipt $purchaseReceipt)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $purchaseReceipt);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->purchaseReceiptActions->delete($purchaseReceipt);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
