<?php

namespace App\Http\Controllers;

use App\Actions\CustomerGroup\CustomerGroupActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Helpers\HashidsHelper;
use App\Http\Requests\CustomerGroup\CustomerGroupStoreRequest;
use App\Http\Requests\CustomerGroup\CustomerGroupUpdateRequest;
use App\Http\Resources\CustomerGroupResource;
use App\Models\CustomerGroup;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerGroupController extends BaseController
{
    private $customerGroupActions;

    public function __construct(CustomerGroupActions $customerGroupActions)
    {
        parent::__construct();

        $this->customerGroupActions = $customerGroupActions;
    }

    public function store(CustomerGroupStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->customerGroupActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], null,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUnique = $this->customerGroupActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], null,
            );
            if (! $isUnique) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $result = $this->customerGroupActions->create($validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('viewAny', CustomerGroup::class);

        if ($request->filled('company_id')) {
            $request->merge(['company_id' => HashidsHelper::decodeId($request->company_id)]);
        }
        if ($request->filled('include_id')) {
            $request->merge(['include_id' => HashidsHelper::decodeId($request->include_id)]);
        }

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],

            'search' => ['nullable', 'string'],
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'include_id' => ['nullable', 'integer', 'exists:customer_groups,id'],

            'refresh' => ['required', 'boolean'],
            'paginate' => ['nullable', 'array', 'required_without:get', 'prohibits:get'],
            'paginate.page' => ['required_with:paginate', 'integer', 'min:1'],
            'paginate.per_page' => ['required_with:paginate', 'integer', 'min:10'],
            'get' => ['nullable', 'array', 'required_without:paginate', 'prohibits:paginate'],
            'get.limit' => ['required_with:get', 'integer', 'min:10'],
        ]);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->customerGroupActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],
                search: $validatedRequest['search'] ?? null,
                companyId: $validatedRequest['company_id'],
                includeId: $validatedRequest['include_id'] ?? null,
                execute: new ExecuteDTO(
                    useCache: ! $validatedRequest['refresh'],
                    pagination: isset($validatedRequest['paginate']) ? new ExecutePaginationDTO(
                        page: $validatedRequest['paginate']['page'],
                        perPage: $validatedRequest['paginate']['per_page'],
                    ) : null,
                    get: isset($validatedRequest['get']) ? new ExecuteGetDTO(
                        limit: $validatedRequest['get']['limit'],
                    ) : null,
                )
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return CustomerGroupResource::collection($result);
        }
    }

    public function read(CustomerGroup $customerGroup)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('view', $customerGroup);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->customerGroupActions->read($customerGroup);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return new CustomerGroupResource($result);
        }
    }

    public function update(CustomerGroup $customerGroup, CustomerGroupUpdateRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->customerGroupActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], $customerGroup->id,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUnique = $this->customerGroupActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], $customerGroup->id,
            );
            if (! $isUnique) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $result = $this->customerGroupActions->update(
                customerGroup: $customerGroup,
                data: $validatedRequest
            );

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(CustomerGroup $customerGroup)
    {
        if (! Auth::check()) return response()->error(trans('rules.auth.unauthorized'), 401);
        $this->authorize('delete', $customerGroup);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->customerGroupActions->delete($customerGroup);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
