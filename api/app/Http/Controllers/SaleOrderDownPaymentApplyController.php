<?php

namespace App\Http\Controllers;

use App\Actions\SaleOrderDownPaymentApply\SaleOrderDownPaymentApplyActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\SaleOrderDownPaymentApply\SaleOrderDownPaymentApplyStoreRequest;
use App\Http\Requests\SaleOrderDownPaymentApply\SaleOrderDownPaymentApplyUpdateRequest;
use App\Http\Resources\SaleOrderDownPaymentApplyResource;
use App\Models\SaleOrderDownPaymentApply;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleOrderDownPaymentApplyController extends BaseController
{
    private $saleOrderDownPaymentApplyActions;

    public function __construct(SaleOrderDownPaymentApplyActions $saleOrderDownPaymentApplyActions)
    {
        parent::__construct();

        $this->saleOrderDownPaymentApplyActions = $saleOrderDownPaymentApplyActions;
    }

    public function store(SaleOrderDownPaymentApplyStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->saleOrderDownPaymentApplyActions->create($validatedRequest);
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
        $this->authorize('viewAny', SaleOrderDownPaymentApply::class);

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
            $result = $this->saleOrderDownPaymentApplyActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                saleOrderId: $validatedRequest['sale_order_id'] ?? null,
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

        return SaleOrderDownPaymentApplyResource::collection($result);
    }

    public function read(SaleOrderDownPaymentApply $saleOrderDownPaymentApply)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $saleOrderDownPaymentApply);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->saleOrderDownPaymentApplyActions->read($saleOrderDownPaymentApply);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new SaleOrderDownPaymentApplyResource($result);
    }

    public function update(SaleOrderDownPaymentApply $saleOrderDownPaymentApply, SaleOrderDownPaymentApplyUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->saleOrderDownPaymentApplyActions->update(
                saleOrderDownPaymentApply: $saleOrderDownPaymentApply,
                data: $validatedRequest
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(SaleOrderDownPaymentApply $saleOrderDownPaymentApply)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $saleOrderDownPaymentApply);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->saleOrderDownPaymentApplyActions->delete($saleOrderDownPaymentApply);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
