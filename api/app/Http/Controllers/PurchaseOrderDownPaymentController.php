<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseOrderDownPayment\PurchaseOrderDownPaymentActions;
use App\Enums\RecordStatusEnum;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseOrderDownPayment\PurchaseOrderDownPaymentStoreRequest;
use App\Http\Requests\PurchaseOrderDownPayment\PurchaseOrderDownPaymentUpdateRequest;
use App\Http\Resources\PurchaseOrderDownPaymentResource;
use App\Models\PurchaseOrderDownPayment;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseOrderDownPaymentController extends BaseController
{
    private $purchaseOrderDownPaymentActions;

    public function __construct(PurchaseOrderDownPaymentActions $purchaseOrderDownPaymentActions)
    {
        parent::__construct();

        $this->purchaseOrderDownPaymentActions = $purchaseOrderDownPaymentActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) {
            return response()->error(trans('rules.auth.unauthorized'), 401);
        }
        $this->authorize('viewAny', PurchaseOrderDownPayment::class);

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
            $result = $this->purchaseOrderDownPaymentActions->readAny(
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
            $response = PurchaseOrderDownPaymentResource::collection($result);

            return $response;
        }
    }

    public function read(PurchaseOrderDownPayment $purchaseOrderDownPayment)
    {
        if (! Auth::check()) {
            return response()->error(trans('rules.auth.unauthorized'), 401);
        }
        $this->authorize('view', $purchaseOrderDownPayment);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderDownPaymentActions->read($purchaseOrderDownPayment);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new PurchaseOrderDownPaymentResource($result);

            return $response;
        }
    }

    public function store(PurchaseOrderDownPaymentStoreRequest $purchaseOrderDownPaymentRequest)
    {
        $request = $purchaseOrderDownPaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderDownPaymentActions->create($request);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PurchaseOrderDownPayment $purchaseOrderDownPayment, PurchaseOrderDownPaymentUpdateRequest $purchaseOrderDownPaymentRequest)
    {
        $request = $purchaseOrderDownPaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseOrderDownPaymentActions->update(
                purchaseOrderDownPayment: $purchaseOrderDownPayment,
                data: $request
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseOrderDownPayment $purchaseOrderDownPayment)
    {
        if (! Auth::check()) {
            return response()->error(trans('rules.auth.unauthorized'), 401);
        }
        $this->authorize('delete', $purchaseOrderDownPayment);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->purchaseOrderDownPaymentActions->delete($purchaseOrderDownPayment);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
