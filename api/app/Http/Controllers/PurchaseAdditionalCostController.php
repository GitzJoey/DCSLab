<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseAdditionalCost\PurchaseAdditionalCostActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseAdditionalCost\PurchaseAdditionalCostStoreRequest;
use App\Http\Requests\PurchaseAdditionalCost\PurchaseAdditionalCostUpdateRequest;
use App\Http\Resources\PurchaseAdditionalCostResource;
use App\Models\PurchaseAdditionalCost;
use App\Rules\ExistsForCompany;
use App\Rules\IsValidBranch;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseAdditionalCostController extends BaseController
{
    public function __construct(
        private readonly PurchaseAdditionalCostActions $purchaseAdditionalCostActions,
    ) {
        parent::__construct();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PurchaseAdditionalCost::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'purchase_id' => $request->filled('purchase_id') ? HashidsHelper::decodeId($request->purchase_id) : null,
            'purchase_additional_cost_category_id' => $request->filled('purchase_additional_cost_category_id') ? HashidsHelper::decodeId($request->purchase_additional_cost_category_id) : null,
            'include_id' => $request->filled('include_id') ? HashidsHelper::decodeId($request->include_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'purchase_id' => ['nullable', 'integer', new ExistsForCompany('purchases', $request->company_id)],
            'purchase_additional_cost_category_id' => ['nullable', 'integer', new ExistsForCompany('purchase_additional_cost_categories', $request->company_id)],
            'is_amount_payable_paid_off' => ['nullable', 'boolean'],
            'include_id' => ['nullable', 'integer'],

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
            $result = $this->purchaseAdditionalCostActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                purchaseId: $validatedRequest['purchase_id'] ?? null,
                categoryId: $validatedRequest['purchase_additional_cost_category_id'] ?? null,
                isAmountPayablePaidOff: $validatedRequest['is_amount_payable_paid_off'] ?? null,
                includeId: $validatedRequest['include_id'] ?? null,

                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: isset($validatedRequest['paginate'])
                        ? new ExecutePaginationDTO(
                            page: $validatedRequest['paginate']['page'],
                            perPage: $validatedRequest['paginate']['per_page'],
                        )
                        : null,
                    get: isset($validatedRequest['get'])
                        ? new ExecuteGetDTO(limit: $validatedRequest['get']['limit'])
                        : null,
                ),
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : PurchaseAdditionalCostResource::collection($result);
    }

    public function read(PurchaseAdditionalCost $purchase_additional_cost)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $purchase_additional_cost);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseAdditionalCostActions->read($purchase_additional_cost);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : new PurchaseAdditionalCostResource($result);
    }

    public function store(PurchaseAdditionalCostStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->purchaseAdditionalCostActions->isUniqueCode(
                $validatedRequest['company_id'],
                $validatedRequest['code'],
                null,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            DB::beginTransaction();
            $result = $this->purchaseAdditionalCostActions->create($validatedRequest);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PurchaseAdditionalCost $purchase_additional_cost, PurchaseAdditionalCostUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            $isUniqueCode = $this->purchaseAdditionalCostActions->isUniqueCode(
                $purchase_additional_cost->company_id,
                $validatedRequest['code'],
                $purchase_additional_cost->id,
            );
            if (! $isUniqueCode) return response()->error(['code' => [trans('rules.unique_code')]], 422);

            DB::beginTransaction();
            $result = $this->purchaseAdditionalCostActions->update($purchase_additional_cost, $validatedRequest);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseAdditionalCost $purchase_additional_cost)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $purchase_additional_cost);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();
            $result = $this->purchaseAdditionalCostActions->delete($purchase_additional_cost);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
