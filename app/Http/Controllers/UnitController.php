<?php

namespace App\Http\Controllers;

use App\Services\UnitService;
use App\Http\Requests\UnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use Exception;

class UnitController extends BaseController
{
    private $unitService;

    public function __construct(UnitService $unitService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->unitService = $unitService;
    }

    public function list(UnitRequest $unitRequest)
    {
        $request = $unitRequest->validated();

        $companyId = $request['company_id'];
        $category = array_key_exists('category', $request) ? $request['category'] : null;
        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $result = $this->unitService->list(
            companyId: $companyId, 
            category: $category, 
            search: $search, 
            paginate: $paginate, 
            page: $page,
            perPage: $perPage
        );

        if (is_null($result)) {
            return response()->error();
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
            $result = $this->unitService->read($unit);
        } catch(Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }
        
        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new UnitResource($result);
            return $response;    
        }
    }

    public function store(UnitRequest $unitRequest)
    {
        $request = $unitRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->unitService->generateUniqueCode();
            } while (!$this->unitService->isUniqueCode($code, $company_id));
        } else {
            if (!$this->unitService->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $unitArr = [
            'company_id' => $company_id,
            'code' => $code,
            'company_id' => $request['company_id'],
            'name' => $request['name'],
            'description' => $request['description'],
            'category' => $request['category']
        ];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->unitService->create($unitArr);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(Unit $unit, UnitRequest $unitRequest)
    {
        $request = $unitRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->unitService->generateUniqueCode();
            } while (!$this->unitService->isUniqueCode($code, $company_id, $unit->id));
        } else {
            if (!$this->unitService->isUniqueCode($code, $company_id, $unit->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $unitArr = [
            'code' => $code,
            'name' => $request['name'],
            'description' => $request['description'],
            'category' => $request['category']
        ];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->unitService->update(
                $unit,
                $unitArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }
    
    public function delete(Unit $unit)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->unitService->delete($unit);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return !$result ? response()->error($errorMsg) : response()->success();
    }
}