<?php

namespace App\Http\Controllers;

use App\Actions\SaleOrderDownPayment\SaleOrderDownPaymentActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\SaleOrderDownPayment\SaleOrderDownPaymentStoreRequest;
use App\Http\Requests\SaleOrderDownPayment\SaleOrderDownPaymentUpdateRequest;
use App\Http\Resources\SaleOrderDownPaymentResource;
use App\Models\SaleOrderDownPayment;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleOrderDownPaymentController extends BaseController
{
    private $saleOrderDownPaymentActions;

    public function __construct(SaleOrderDownPaymentActions $saleOrderDownPaymentActions)
    {
        parent::__construct();

        $this->saleOrderDownPaymentActions = $saleOrderDownPaymentActions;
    }

    public function store(SaleOrderDownPaymentStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->saleOrderDownPaymentActions->create($validatedRequest);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', SaleOrderDownPayment::class);

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
            $result = $this->saleOrderDownPaymentActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,
                salesOrderId: $validatedRequest['sales_order_id'] ?? null,
                cashAccountId: $validatedRequest['cash_account_id'] ?? null,
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

        return SaleOrderDownPaymentResource::collection($result);
    }

    public function read(SaleOrderDownPayment $saleOrderDownPayment)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $saleOrderDownPayment);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleOrderDownPaymentActions->read($saleOrderDownPayment);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new SaleOrderDownPaymentResource($result);
    }

    public function update(SaleOrderDownPayment $saleOrderDownPayment, SaleOrderDownPaymentUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->saleOrderDownPaymentActions->update(
                saleOrderDownPayment: $saleOrderDownPayment,
                data: $validatedRequest
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(SaleOrderDownPayment $saleOrderDownPayment)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $saleOrderDownPayment);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->saleOrderDownPaymentActions->delete($saleOrderDownPayment);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
