<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseOrderDownPaymentApply\PurchaseOrderDownPaymentApplyActions;
use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseOrderDownPaymentApply\PurchaseOrderDownPaymentApplyStoreRequest;
use App\Http\Requests\PurchaseOrderDownPaymentApply\PurchaseOrderDownPaymentApplyUpdateRequest;
use App\Http\Resources\PurchaseOrderDownPaymentApplyResource;
use App\Models\PurchaseOrderDownPaymentApply;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderDownPaymentApplyController extends BaseController
{
    private $purchaseOrderDownPaymentApplyActions;

    public function __construct(PurchaseOrderDownPaymentApplyActions $purchaseOrderDownPaymentApplyActions)
    {
        parent::__construct();

        $this->purchaseOrderDownPaymentApplyActions = $purchaseOrderDownPaymentApplyActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) {
            return response()->error(trans('rules.auth.unauthorized'), 401);
        }
        $this->authorize('viewAny', PurchaseOrderDownPaymentApply::class);

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
            $result = $this->purchaseOrderDownPaymentApplyActions->readAny(
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
            $response = PurchaseOrderDownPaymentApplyResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseOrderDownPaymentApply $purchaseOrderDownPaymentApply)
    {
        if (! Auth::check()) {
            return response()->error(trans('rules.auth.unauthorized'), 401);
        }
        $this->authorize('view', $purchaseOrderDownPaymentApply);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderDownPaymentApplyActions->read($purchaseOrderDownPaymentApply);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseOrderDownPaymentApplyResource($result);

            return $response;
        }
    }

    public function store(PurchaseOrderDownPaymentApplyStoreRequest $purchaseOrderDownPaymentApplyRequest)
    {
        $request = $purchaseOrderDownPaymentApplyRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseOrderDownPaymentApplyActions->create($request);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PurchaseOrderDownPaymentApply $purchaseOrderDownPaymentApply, PurchaseOrderDownPaymentApplyUpdateRequest $purchaseOrderDownPaymentApplyRequest)
    {
        $request = $purchaseOrderDownPaymentApplyRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseOrderDownPaymentApplyActions->update(
                purchaseOrderDownPaymentApply: $purchaseOrderDownPaymentApply,
                data: $request
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseOrderDownPaymentApply $purchaseOrderDownPaymentApply)
    {
        if (! Auth::check()) {
            return response()->error(trans('rules.auth.unauthorized'), 401);
        }
        $this->authorize('delete', $purchaseOrderDownPaymentApply);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseOrderDownPaymentApplyActions->delete($purchaseOrderDownPaymentApply);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
