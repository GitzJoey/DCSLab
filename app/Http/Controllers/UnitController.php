<?php

namespace App\Http\Controllers;

use App\Services\UnitService;
use App\Http\Requests\UnitRequest;
use App\Http\Resources\UnitResource;

class UnitController extends BaseController
{
    private $unitService;

    public function __construct(UnitService $unitService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->unitService = $unitService;
    }

    public function read(UnitRequest $unitRequest)
    {
        $request = $unitRequest->validated();

        $companyId = $request['company_id'];
        $category = array_key_exists('category', $request) ? $request['category'] : null;
        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $result = $this->unitService->read(
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

    public function store(UnitRequest $unitRequest)
    {
        $request = $unitRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('const.DEFAULT.KEYWORDS.AUTO')) {
            do {
                $code = $this->unitService->generateUniqueCode($company_id);
            } while (!$this->unitService->isUniqueCode($code, $company_id));
        } else {
            if (!$this->unitService->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $name = $request['name'];
        $description = $request['description'];
        $category = $request['category'];

        $result = $this->unitService->create(
            $company_id,
            $code, 
            $name,
            $description,
            $category
        );

        return is_null($result) ? response()->error() : response()->success();
    }

    public function update($id, UnitRequest $unitRequest)
    {
        $request = $unitRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('const.DEFAULT.KEYWORDS.AUTO')) {
            do {
                $code = $this->unitService->generateUniqueCode($company_id);
            } while (!$this->unitService->isUniqueCode($code, $company_id, $id));
        } else {
            if (!$this->unitService->isUniqueCode($code, $company_id, $id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $name = $request['name'];
        $description = $request['description'];
        $category = $request['category'];

        $result = $this->unitService->update(
            id: $id,
            company_id: $company_id,
            code: $code, 
            name: $name,
            description: $description,
            category: $category
        );

        return is_null($result) ? response()->error() : response()->success();
    }
    
    public function delete($id)
    {
        $result = $this->unitService->delete($id);

        return !$result ? response()->error() : response()->success();
    }
}