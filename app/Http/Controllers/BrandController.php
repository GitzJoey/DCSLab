<?php

namespace App\Http\Controllers;

use App\Services\BrandService;
use App\Http\Requests\BrandRequest;
use App\Http\Resources\BrandResource;

class BrandController extends BaseController
{
    private $brandService;

    public function __construct(BrandService $brandService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->brandService = $brandService;
    }

    public function read(BrandRequest $brandRequest)
    {
        $request = $brandRequest->validated();

        $companyId = $request['company_id'];
        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $result = $this->brandService->read(
            companyId: $companyId,
            search: $search, 
            paginate: $paginate, 
            page: $page,
            perPage: $perPage
        );

        if (is_null($result)) {
            return response()->error();
        } else {
            $response = BrandResource::collection($result);

            return $response;
        }
    }

    public function store(BrandRequest $brandRequest)
    {
        $request = $brandRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('const.DEFAULT.KEYWORDS.AUTO')) {
            do {
                $code = $this->brandService->generateUniqueCode($company_id);
            } while (!$this->brandService->isUniqueCode($code, $company_id));
        } else {
            if (!$this->brandService->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $name = $request['name'];

        $result = $this->brandService->create(
            $company_id,
            $code, 
            $name
        );

        return is_null($result) ? response()->error() : response()->success();
    }

    public function update($id, BrandRequest $brandRequest)
    {
        $request = $brandRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('const.DEFAULT.KEYWORDS.AUTO')) {
            do {
                $code = $this->brandService->generateUniqueCode($company_id);
            } while (!$this->brandService->isUniqueCode($code, $company_id, $id));
        } else {
            if (!$this->brandService->isUniqueCode($code, $company_id, $id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $name = $request['name'];

        $result = $this->brandService->update(
            id: $id,
            company_id: $company_id,
            code: $code, 
            name: $name
        );

        return is_null($result) ? response()->error() : response()->success();
    }
    
    public function delete($id)
    {
        $result = $this->brandService->delete($id);

        return !$result ? response()->error() : response()->success();
    }
}