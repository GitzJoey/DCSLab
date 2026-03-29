<?php

namespace App\Http\Controllers;

use App\Actions\PurchaseReturnAdditionalCost\PurchaseReturnAdditionalCostActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\PurchaseReturnAdditionalCost\PurchaseReturnAdditionalCostStoreRequest;
use App\Http\Requests\PurchaseReturnAdditionalCost\PurchaseReturnAdditionalCostUpdateRequest;
use App\Http\Resources\PurchaseReturnAdditionalCostResource;
use App\Models\PurchaseReturnAdditionalCost;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseReturnAdditionalCostController extends BaseController
{
    private $purchaseReturnAdditionalCostActions;

    public function __construct(PurchaseReturnAdditionalCostActions $purchaseReturnAdditionalCostActions)
    {
        parent::__construct();

        $this->purchaseReturnAdditionalCostActions = $purchaseReturnAdditionalCostActions;
    }

    public function store(PurchaseReturnAdditionalCostStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseReturnAdditionalCostActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    null
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $result = $this->purchaseReturnAdditionalCostActions->create($validatedRequest);

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
        $this->authorize('viewAny', PurchaseReturnAdditionalCost::class);

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
            $result = $this->purchaseReturnAdditionalCostActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                companyId: $validatedRequest['company_id'],
                branchId: $validatedRequest['branch_id'] ?? null,
                search: $validatedRequest['search'] ?? null,

                purchaseId: $validatedRequest['purchase_id'] ?? null,
                categoryId: $validatedRequest['category_id'] ?? null,

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

        return PurchaseReturnAdditionalCostResource::collection($result);
    }

    public function read(PurchaseReturnAdditionalCost $purchaseReturnAdditionalCost)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('view', $purchaseReturnAdditionalCost);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->purchaseReturnAdditionalCostActions->read($purchaseReturnAdditionalCost);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        }

        return new PurchaseReturnAdditionalCostResource($result);
    }

    public function update(PurchaseReturnAdditionalCost $purchaseReturnAdditionalCost, PurchaseReturnAdditionalCostUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->purchaseReturnAdditionalCostActions->isUniqueCode(
                    $validatedRequest['company_id'],
                    $validatedRequest['code'],
                    $purchaseReturnAdditionalCost->id
                );
                if (! $isUnique) {
                    return response()->error(['code' => [trans('rules.unique_code')]], 422);
                }
            }

            $result = $this->purchaseReturnAdditionalCostActions->update(
                purchaseReturnAdditionalCost: $purchaseReturnAdditionalCost,
                data: $validatedRequest
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(PurchaseReturnAdditionalCost $purchaseReturnAdditionalCost)
    {
        if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);
        $this->authorize('delete', $purchaseReturnAdditionalCost);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->purchaseReturnAdditionalCostActions->delete($purchaseReturnAdditionalCost);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
