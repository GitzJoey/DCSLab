<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseReceiptProductUnitSerial\PurchaseReceiptProductUnitSerialActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseReceiptProductUnitSerial\PurchaseReceiptProductUnitSerialStoreRequest;
use App\Http\Requests\PurchaseReceiptProductUnitSerial\PurchaseReceiptProductUnitSerialUpdateRequest;
use App\Http\Resources\PurchaseReceiptProductUnitSerialResource;
use App\Models\PurchaseReceiptProductUnitSerial;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseReceiptProductUnitSerialController extends BaseController
{
    private $purchaseReceiptProductUnitSerialActions;

    public function __construct(PurchaseReceiptProductUnitSerialActions $purchaseReceiptProductUnitSerialActions)
    {
        parent::__construct();

        $this->purchaseReceiptProductUnitSerialActions = $purchaseReceiptProductUnitSerialActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PurchaseReceiptProductUnitSerial::class);

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
            $result = $this->purchaseReceiptProductUnitSerialActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                purchaseReceiptId: $validatedRequest['purchase_receipt_id'] ?? null,
                purchaseReceiptProductUnitId: $validatedRequest['purchase_receipt_product_unit_id'] ?? null,

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

        return PurchaseReceiptProductUnitSerialResource::collection($result);
    }

    public function read(PurchaseReceiptProductUnitSerial $purchaseReceiptProductUnitSerial)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $purchaseReceiptProductUnitSerial);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReceiptProductUnitSerialActions->read($purchaseReceiptProductUnitSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new PurchaseReceiptProductUnitSerialResource($result);
    }

    public function store(PurchaseReceiptProductUnitSerialStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseReceiptProductUnitSerialActions->create($validatedRequest);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PurchaseReceiptProductUnitSerial $purchaseReceiptProductUnitSerial, PurchaseReceiptProductUnitSerialUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseReceiptProductUnitSerialActions->update(
                purchaseReceiptProductUnitSerial: $purchaseReceiptProductUnitSerial,
                data: $validatedRequest
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseReceiptProductUnitSerial $purchaseReceiptProductUnitSerial)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $purchaseReceiptProductUnitSerial);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseReceiptProductUnitSerialActions->delete($purchaseReceiptProductUnitSerial);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
