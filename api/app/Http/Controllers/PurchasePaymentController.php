<?php

namespace App\Http\Controllers;

use App\Actions\PurchasePayment\PurchasePaymentActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchasePayment\PurchasePaymentStoreRequest;
use App\Http\Requests\PurchasePayment\PurchasePaymentUpdateRequest;
use App\Http\Resources\PurchasePaymentResource;
use App\Models\PurchasePayment;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchasePaymentController extends BaseController
{
    private $purchasePaymentActions;

    public function __construct(PurchasePaymentActions $purchasePaymentActions)
    {
        parent::__construct();

        $this->purchasePaymentActions = $purchasePaymentActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PurchasePayment::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'purchase_id' => $request->filled('purchase_id') ? HashidsHelper::decodeId($request->purchase_id) : null,
            'cash_account_id' => $request->filled('cash_account_id') ? HashidsHelper::decodeId($request->cash_account_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'purchase_id' => ['nullable', 'integer', new ExistsForCompany('purchases', $request->company_id)],
            'cash_account_id' => ['nullable', 'integer', new ExistsForCompany('cash_accounts', $request->company_id)],

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
            $result = $this->purchasePaymentActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                startDate: $validatedRequest['start_date'] ?? null,
                endDate: $validatedRequest['end_date'] ?? null,
                purchaseId: $validatedRequest['purchase_id'] ?? null,
                cashAccountId: $validatedRequest['cash_account_id'] ?? null,

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

        return PurchasePaymentResource::collection($result);
    }

    public function read(PurchasePayment $purchasePayment)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $purchasePayment);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchasePaymentActions->read($purchasePayment);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new PurchasePaymentResource($result);
    }

    public function store(PurchasePaymentStoreRequest $purchasePaymentRequest)
    {
        $request = $purchasePaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchasePaymentActions->create($request);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PurchasePayment $purchasePayment, PurchasePaymentUpdateRequest $purchasePaymentRequest)
    {
        $request = $purchasePaymentRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchasePaymentActions->update(
                purchasePayment: $purchasePayment,
                data: $request
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchasePayment $purchasePayment)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $purchasePayment);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->purchasePaymentActions->delete($purchasePayment);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
