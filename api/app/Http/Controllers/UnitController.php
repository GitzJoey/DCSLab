<?php

namespace App\Http\Controllers;

use App\Actions\Unit\UnitActions;
use App\DTOs\ExecuteDTO;
use App\DTOs\ExecuteGetDTO;
use App\DTOs\ExecutePaginationDTO;
use App\Enums\UnitTypeEnum;
use App\Helpers\HashidsHelper;
use App\Http\Requests\Unit\UnitStoreRequest;
use App\Http\Requests\Unit\UnitUpdateRequest;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use App\Rules\IsValidCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnitController extends BaseController
{
    private $unitActions;

    public function __construct(UnitActions $unitActions)
    {
        parent::__construct();

        $this->unitActions = $unitActions;
    }

    public function store(UnitStoreRequest $request)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->unitActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], null,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->unitActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], null,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $result = $this->unitActions->create($validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(Request $request)
    {
        if (! Auth::check()) {
            return response()->error(trans('rules.auth.unauthorized'), 401);
        }
        $this->authorize('viewAny', Unit::class);

        if ($request->filled('company_id')) {
            $request->merge(['company_id' => HashidsHelper::decodeId($request->company_id)]);
        }
        if ($request->filled('include_id')) {
            $request->merge(['include_id' => HashidsHelper::decodeId($request->include_id)]);
        }

        $validatedRequest = $request->validate([
            'with_trashed' => ['required', 'boolean'],

            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'search' => ['nullable', 'string'],
            'include_id' => ['nullable', 'integer', 'exists:units,id'],

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
            $result = $this->unitActions->readAny(
                withTrashed: $validatedRequest['with_trashed'],

                companyId: $validatedRequest['company_id'],
                search: $validatedRequest['search'] ?? null,
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
            return UnitResource::collection($result);
        }
    }

    public function getTypes()
    {
        return [
            ['name' => 'views.unit.enums.unit_type.product', 'code' => UnitTypeEnum::PRODUCT->value],
            ['name' => 'views.unit.enums.unit_type.service', 'code' => UnitTypeEnum::SERVICE->value],
        ];
    }

    public function read(Unit $unit)
    {
        $this->authorize('view', $unit);

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->unitActions->read($unit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return new UnitResource($result);
        }
    }

    public function update(UnitUpdateRequest $request, Unit $unit)
    {
        $validatedRequest = $request->validated();

        $result = null;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            if ($validatedRequest['code'] !== config('dcslab.KEYWORDS.AUTO')) {
                $isUnique = $this->unitActions->isUniqueCode(
                    $validatedRequest['company_id'], $validatedRequest['code'], $unit->id,
                );
                if (! $isUnique) return response()->error(['code' => [trans('rules.unique_code')]], 422);
            }

            $isUniqueName = $this->unitActions->isUniqueName(
                $validatedRequest['company_id'], $validatedRequest['name'], $unit->id,
            );
            if (! $isUniqueName) return response()->error(['name' => [trans('rules.unique_name')]], 422);

            $result = $this->unitActions->update($unit, $validatedRequest);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Unit $unit)
    {
        $this->authorize('delete', $unit);

        $result = false;
        $errorMsg = '';

        try {
            DB::beginTransaction();

            $result = $this->unitActions->delete($unit);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
