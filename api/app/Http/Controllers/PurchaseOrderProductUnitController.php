<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseOrderProductUnit\PurchaseOrderProductUnitActions;
use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseOrderProductUnit\PurchaseOrderProductUnitStoreRequest;
use App\Http\Requests\PurchaseOrderProductUnit\PurchaseOrderProductUnitUpdateRequest;
use App\Http\Resources\PurchaseOrderProductUnitResource;
use App\Models\PurchaseOrderProductUnit;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderProductUnitController extends BaseController
{
    private $purchaseOrderProductUnitActions;

    public function __construct(PurchaseOrderProductUnitActions $purchaseOrderProductUnitActions)
    {
        parent::__construct();

        $this->purchaseOrderProductUnitActions = $purchaseOrderProductUnitActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) {
            return response()->error(trans('rules.auth.unauthorized'), 401);
        }
        $this->authorize('viewAny', PurchaseOrderProductUnit::class);

        if ($request->filled('company_id')) {
            $request->merge(['company_id' => HashidsHelper::decodeId($request->company_id)]);
        }
        if ($request->filled('status')) {
            $request->merge([
                'status' => RecordStatusEnum::isValid($request->status)
                    ? RecordStatusEnum::resolveToEnum($request->status)->value
                    : -1,
            ]);
        }

        $validatedRequest = $request->validate([
            'refresh' => ['required', 'boolean'],
            'with_trashed' => ['required', 'boolean'],
            'search' => ['nullable', 'string'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'status' => ['nullable', 'integer', 'in:'.implode(',', RecordStatusEnum::toArrayValue())],
            'paginate' => ['required', 'boolean'],
            'page' => ['nullable', 'required_if:paginate,true', 'numeric', 'min:1'],
            'per_page' => ['nullable', 'required_if:paginate,true', 'numeric', 'min:10'],
            'limit' => ['nullable', 'integer', 'min:1'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderProductUnitActions->readAny(
                useCache: $validatedRequest['refresh'],
                withTrashed: $validatedRequest['with_trashed'],
                search: $validatedRequest['search'],
                companyId: $validatedRequest['company_id'],
                paginate: $validatedRequest['paginate'],
                page: $validatedRequest['page'],
                perPage: $validatedRequest['per_page'],
                limit: $validatedRequest['limit'],
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = PurchaseOrderProductUnitResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseOrderProductUnit $purchaseOrderProductUnit)
    {
        if (! Auth::check()) {
            return response()->error(trans('rules.auth.unauthorized'), 401);
        }
        $this->authorize('view', $purchaseOrderProductUnit);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderProductUnitActions->read($purchaseOrderProductUnit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseOrderProductUnitResource($result);

            return $response;
        }
    }

    public function store(PurchaseOrderProductUnitStoreRequest $purchaseOrderProductUnitRequest)
    {
        $request = $purchaseOrderProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseOrderProductUnitActions->create($request);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PurchaseOrderProductUnit $purchaseOrderProductUnit, PurchaseOrderProductUnitUpdateRequest $purchaseOrderProductUnitRequest)
    {
        $request = $purchaseOrderProductUnitRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseOrderProductUnitActions->update(
                purchaseOrderProductUnit: $purchaseOrderProductUnit,
                data: $request
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseOrderProductUnit $purchaseOrderProductUnit)
    {
        if (! Auth::check()) {
            return response()->error(trans('rules.auth.unauthorized'), 401);
        }
        $this->authorize('delete', $purchaseOrderProductUnit);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseOrderProductUnitActions->delete($purchaseOrderProductUnit);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
