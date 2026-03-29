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
    private $purchaseAdditionalCostActions;

    public function __construct(PurchaseAdditionalCostActions $purchaseAdditionalCostActions)
    {
        parent::__construct();

        $this->purchaseAdditionalCostActions = $purchaseAdditionalCostActions;
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('viewAny', PurchaseAdditionalCost::class);

        $request->merge([
            'company_id' => $request->filled('company_id') ? HashidsHelper::decodeId($request->company_id) : null,
            'branch_id' => $request->filled('branch_id') ? HashidsHelper::decodeId($request->branch_id) : null,
            'purchase_id' => $request->filled('purchase_id') ? HashidsHelper::decodeId($request->purchase_id) : null,
            'category_id' => $request->filled('category_id') ? HashidsHelper::decodeId($request->category_id) : null,
        ]);

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'branch_id' => ['nullable', 'integer', new IsValidBranch($request->company_id, false)],
            'search' => ['nullable', 'string'],

            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'purchase_id' => ['nullable', 'integer', new ExistsForCompany('purchases', $request->company_id)],
            'category_id' => ['nullable', 'integer', new ExistsForCompany('purchase_additional_cost_categories', $request->company_id)],

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

                startDate: $validatedRequest['start_date'] ?? null,
                endDate: $validatedRequest['end_date'] ?? null,
                purchaseId: $validatedRequest['purchase_id'] ?? null,
                categoryId: $validatedRequest['category_id'] ?? null,

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

        return PurchaseAdditionalCostResource::collection($result);
    }

    public function read(PurchaseAdditionalCost $purchaseAdditionalCost)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $purchaseAdditionalCost);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseAdditionalCostActions->read($purchaseAdditionalCost);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new PurchaseAdditionalCostResource($result);
    }

    public function store(PurchaseAdditionalCostStoreRequest $purchaseAdditionalCostRequest)
    {
        $request = $purchaseAdditionalCostRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseAdditionalCostActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    null
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $result = $this->purchaseAdditionalCostActions->create($request);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(PurchaseAdditionalCost $purchaseAdditionalCost, PurchaseAdditionalCostUpdateRequest $purchaseAdditionalCostRequest)
    {
        $request = $purchaseAdditionalCostRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($request['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseAdditionalCostActions->isUniqueCode(
                    $request['company_id'],
                    $request['code'],
                    $purchaseAdditionalCost->id
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $result = $this->purchaseAdditionalCostActions->update(
                purchaseAdditionalCost: $purchaseAdditionalCost,
                data: $request
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseAdditionalCost $purchaseAdditionalCost)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $purchaseAdditionalCost);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->purchaseAdditionalCostActions->delete($purchaseAdditionalCost);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
