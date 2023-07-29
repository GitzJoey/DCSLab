<?php

namespace App\Http\Controllers;

use App\Actions\Unit\UnitActions;
use App\Enums\UnitCategory;
use App\Http\Requests\UnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use Exception;

class UnitController extends BaseController
{
    private $unitActions;

    public function __construct(UnitActions $unitActions)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->unitActions = $unitActions;
    }

    public function store(UnitRequest $unitRequest)
    {
        $request = $unitRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->unitActions->generateUniqueCode();
            } while (! $this->unitActions->isUniqueCode($code, $company_id));
        } else {
            if (! $this->unitActions->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $unitArr = [
            'company_id' => $company_id,
            'code' => $code,
            'company_id' => $request['company_id'],
            'name' => $request['name'],
            'description' => $request['description'],
            'category' => $request['category'],
        ];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->unitActions->create($unitArr);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(UnitRequest $unitRequest)
    {
        $request = $unitRequest->validated();

        $companyId = $request['company_id'];

        $category = null;
        if (array_key_exists('category', $request)) {
            $category = $request['category'];
        }

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('per_page', $request) ? abs($request['per_page']) : 10;
        $useCache = array_key_exists('refresh', $request) ? boolval($request['refresh']) : true;

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->unitActions->readAny(
                companyId: $companyId,
                category: $category,
                search: $search,
                paginate: $paginate,
                page: $page,
                perPage: $perPage,
                useCache: $useCache
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = UnitResource::collection($result);

            return $response;
        }
    }

    public function read(Unit $unit, UnitRequest $unitRequest)
    {
        $request = $unitRequest->validated();

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
            $response = new UnitResource($result);

            return $response;
        }
    }

    public function update(Unit $unit, UnitRequest $unitRequest)
    {
        $request = $unitRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->unitActions->generateUniqueCode();
            } while (! $this->unitActions->isUniqueCode($code, $company_id, $unit->id));
        } else {
            if (! $this->unitActions->isUniqueCode($code, $company_id, $unit->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $unitArr = [
            'code' => $code,
            'name' => $request['name'],
            'description' => $request['description'],
            'category' => $request['category'],
        ];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->unitActions->update(
                $unit,
                $unitArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Unit $unit, UnitRequest $unitRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->unitActions->delete($unit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }

    public function getUnitCategory()
    {
        return [
            ['name' => 'components.dropdown.values.unitCategoryDDL.product', 'code' => UnitCategory::PRODUCTS->name],
            ['name' => 'components.dropdown.values.unitCategoryDDL.service', 'code' => UnitCategory::SERVICES->name],
        ];
    }

    public function getUnitDDL(UnitRequest $unitRequest)
    {
        $request = $unitRequest->validated();

        $result = null;
        $errorMsg = '';

        $category = null;
        if (array_key_exists('category', $request)) {
            $category = $request['category'];
        }

        try {
            $result = $this->unitActions->getUnitDDL(
                $request['company_id'],
                $category
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return $result;
        }
    }
}
