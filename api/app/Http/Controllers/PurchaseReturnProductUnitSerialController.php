<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseReturnProductUnitSerial\PurchaseReturnProductUnitSerialActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseReturnProductUnitSerial\PurchaseReturnProductUnitSerialStoreRequest;
use App\Http\Requests\PurchaseReturnProductUnitSerial\PurchaseReturnProductUnitSerialUpdateRequest;
use App\Http\Resources\PurchaseReturnProductUnitSerialResource;
use App\Models\PurchaseReturnProductUnitSerial;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseReturnProductUnitSerialController extends BaseController
{
    private $purchaseReturnProductUnitSerialActions;

    public function __construct(PurchaseReturnProductUnitSerialActions $purchaseReturnProductUnitSerialActions)
    {
        parent::__construct();

        $this->purchaseReturnProductUnitSerialActions = $purchaseReturnProductUnitSerialActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PurchaseReturnProductUnitSerial::class);

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
            $result = $this->purchaseReturnProductUnitSerialActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                purchaseId: $validatedRequest['purchase_id'] ?? null,
                purchaseReturnProductUnitId: $validatedRequest['purchase_return_product_unit_id'] ?? null,

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

        return PurchaseReturnProductUnitSerialResource::collection($result);
    }

    public function read(PurchaseReturnProductUnitSerial $purchaseReturnProductUnitSerial)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $purchaseReturnProductUnitSerial);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnProductUnitSerialActions->read($purchaseReturnProductUnitSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new PurchaseReturnProductUnitSerialResource($result);
    }

    public function store(PurchaseReturnProductUnitSerialStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseReturnProductUnitSerialActions->create($validatedRequest);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PurchaseReturnProductUnitSerial $purchaseReturnProductUnitSerial, PurchaseReturnProductUnitSerialUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseReturnProductUnitSerialActions->update(
                purchaseReturnProductUnitSerial: $purchaseReturnProductUnitSerial,
                data: $validatedRequest
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseReturnProductUnitSerial $purchaseReturnProductUnitSerial)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $purchaseReturnProductUnitSerial);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseReturnProductUnitSerialActions->delete($purchaseReturnProductUnitSerial);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
