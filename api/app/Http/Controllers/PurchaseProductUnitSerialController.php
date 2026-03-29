<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseProductUnitSerial\PurchaseProductUnitSerialActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseProductUnitSerial\PurchaseProductUnitSerialStoreRequest;
use App\Http\Requests\PurchaseProductUnitSerial\PurchaseProductUnitSerialUpdateRequest;
use App\Http\Resources\PurchaseProductUnitSerialResource;
use App\Models\PurchaseProductUnitSerial;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseProductUnitSerialController extends BaseController
{
    private $purchaseProductUnitSerialActions;

    public function __construct(PurchaseProductUnitSerialActions $purchaseProductUnitSerialActions)
    {
        parent::__construct();

        $this->purchaseProductUnitSerialActions = $purchaseProductUnitSerialActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PurchaseProductUnitSerial::class);

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
            $result = $this->purchaseProductUnitSerialActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                purchaseId: $validatedRequest['purchase_id'] ?? null,
                purchaseProductUnitId: $validatedRequest['purchase_product_unit_id'] ?? null,

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

        return PurchaseProductUnitSerialResource::collection($result);
    }

    public function read(PurchaseProductUnitSerial $purchaseProductUnitSerial)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $purchaseProductUnitSerial);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseProductUnitSerialActions->read($purchaseProductUnitSerial);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new PurchaseProductUnitSerialResource($result);
    }

    public function store(PurchaseProductUnitSerialStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseProductUnitSerialActions->create($validatedRequest);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PurchaseProductUnitSerial $purchaseProductUnitSerial, PurchaseProductUnitSerialUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseProductUnitSerialActions->update(
                purchaseProductUnitSerial: $purchaseProductUnitSerial,
                data: $validatedRequest
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseProductUnitSerial $purchaseProductUnitSerial)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $purchaseProductUnitSerial);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseProductUnitSerialActions->delete($purchaseProductUnitSerial);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
