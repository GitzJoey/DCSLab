<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseReceiptProductUnit\PurchaseReceiptProductUnitActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseReceiptProductUnit\PurchaseReceiptProductUnitStoreRequest;
use App\Http\Requests\PurchaseReceiptProductUnit\PurchaseReceiptProductUnitUpdateRequest;
use App\Http\Resources\PurchaseReceiptProductUnitResource;
use App\Models\PurchaseReceiptProductUnit;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseReceiptProductUnitController extends BaseController
{
    private $purchaseReceiptProductUnitActions;

    public function __construct(PurchaseReceiptProductUnitActions $purchaseReceiptProductUnitActions)
    {
        parent::__construct();

        $this->purchaseReceiptProductUnitActions = $purchaseReceiptProductUnitActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PurchaseReceiptProductUnit::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],
            'refresh' => ['required', 'boolean'],
            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:10'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:1'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptProductUnitActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                purchaseReceiptId: $validatedRequest['purchase_receipt_id'] ?? null,
                purchaseId: $validatedRequest['purchase_id'] ?? null,
                productId: $validatedRequest['product_id'] ?? null,
                productUnitId: $validatedRequest['product_unit_id'] ?? null,

                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: isset($validatedRequest['paginate']) ? new ExecutePaginationDTO(
                        page: $validatedRequest['paginate']['page'],
                        perPage: $validatedRequest['paginate']['per_page'],
                    ) : null,
                    get: isset($validatedRequest['get']) ? new ExecuteGetDTO(
                        limit: $validatedRequest['get']['limit'],
                    ) : null,
                ),
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return PurchaseReceiptProductUnitResource::collection($result);
    }

    public function read(PurchaseReceiptProductUnit $purchaseReceiptProductUnit)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $purchaseReceiptProductUnit);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptProductUnitActions->read($purchaseReceiptProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new PurchaseReceiptProductUnitResource($result);
    }

    public function store(PurchaseReceiptProductUnitStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseReceiptProductUnitActions->create($validatedRequest);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PurchaseReceiptProductUnit $purchaseReceiptProductUnit, PurchaseReceiptProductUnitUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseReceiptProductUnitActions->update(
                purchaseReceiptProductUnit: $purchaseReceiptProductUnit,
                data: $validatedRequest
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseReceiptProductUnit $purchaseReceiptProductUnit)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $purchaseReceiptProductUnit);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseReceiptProductUnitActions->delete($purchaseReceiptProductUnit);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
