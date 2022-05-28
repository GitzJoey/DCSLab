<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\BranchService;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Requests\BranchRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\BranchResource;

class BranchController extends BaseController
{
    private $branchService;
    
    public function __construct(BranchService $branchService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->branchService = $branchService;
    }

    public function read(Request $request)
    {
        $search = $request->has('search') && !is_null($request['search']) ? $request['search']:'';
        $search = !is_null($search) ? $search : '';

        $paginate = $request->has('paginate') ? $request['paginate']:true;
        $paginate = !is_null($paginate) ? $paginate : true;
        $paginate = is_numeric($paginate) ? abs($paginate) : true;

        $page = $request->has('page') ? $request['page']:1;
        $page = !is_null($page) ? $page : 1;
        $page = is_numeric($page) ? abs($page) : 1; 

        $perPage = $request->has('perPage') ? $request['perPage']:10;
        $perPage = !is_null($perPage) ? $perPage : 10;
        $perPage = is_numeric($perPage) ? abs($perPage) : 10;  

        $companyId = Hashids::decode($request['companyId'])[0];

        $result = $this->branchService->read(
            companyId: $companyId,
            search: $search,
            paginate: $paginate,
            page: $page,
            perPage: $perPage
        );

        if (is_null($result)) {
            return response()->error();
        } else {
            $response = BranchResource::collection($result);

            return $response;
        }
    }

    public function getBranchByCompanyId(Request $request)
    {
        if ($request->has('companyId')) {
            $result = $this->branchService->getBranchByCompanyId(Hashids::decode($request['companyId'])[0]);
        } else {
            return response()->error();
        }
    
        if (is_null($result)) {
            return response()->error();
        } else {
            $response = BranchResource::collection($result);

            return $response;
        }
    }

    public function getMainBranchByCompanyId(Request $request)
    {
        if ($request->has('companyId')) {
            $result = $this->branchService->getMainBranchByCompanyId(Hashids::decode($request['companyId'])[0]);
        } else {
            return response()->error();
        }
    
        if (is_null($result)) {
            return response()->error();
        } else {
            $response = BranchResource::collection($result);

            return $response;
        }
    }

    public function store(BranchRequest $branchRequest)
    {   
        $request = $branchRequest->validated();
        
        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('const.DEFAULT.KEYWORDS.AUTO')) {
            do {
                $code = $this->branchService->generateUniqueCode($company_id);
            } while (!$this->branchService->isUniqueCode($code, $company_id));
        } else {
            if (!$this->branchService->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $name = $request['name'];
        $address = $request['address'] == '' ? null : $request['address'];
        $city = $request['city'] == '' ? null : $request['city'];
        $contact = $request['contact'] == '' ? null : $request['contact'];

        if (array_key_exists('is_main', $request) && $request['is_main']) {
            $this->branchService->resetMainBranch($company_id);
            $is_main = true;
        } else {
            $is_main = false;
        };

        $remarks = $request['remarks'] == '' ? null : $request['remarks'];
        $status = $request['status'];

        $result = $this->branchService->create(
            $company_id,
            $code, 
            $name,
            $address,
            $city,
            $contact,
            $is_main,
            $remarks,
            $status,
        );

        return is_null($result) ? response()->error() : response()->success();
    }

    public function update($id, BranchRequest $branchRequest)
    {
        $request = $branchRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('const.DEFAULT.KEYWORDS.AUTO')) {
            do {
                $code = $this->branchService->generateUniqueCode($company_id);
            } while (!$this->branchService->isUniqueCode($code, $company_id, $id));
        } else {
            if (!$this->branchService->isUniqueCode($code, $company_id, $id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $name = $request['name'];
        $address = $request['address'] == '' ? null : $request['address'];
        $city = $request['city'] == '' ? null : $request['city'];
        $contact = $request['contact'] == '' ? null : $request['contact'];

        if ($request['is_main'] == true) {
            $this->branchService->resetMainBranch($company_id);
            $is_main = true;
        } else {
            $is_main = false;
        };

        $remarks = $request['remarks'] == '' ? null : $request['remarks'];
        $status = $request['status'];

        $branch = $this->branchService->update(
            $id,
            $company_id,
            $code, 
            $name,
            $address,
            $city,
            $contact,
            $is_main,
            $remarks,
            $status
        );

        return is_null($branch) ? response()->error() : response()->success();
    }

    public function resetMainBranch(Request $request)
    {
        if ($request->has('companyId')) {
            $result = $this->branchService->resetMainBranch(Hashids::decode($request['companyId'])[0]);
        } else {
            return response()->error();
        }
    
        if (is_null($result)) {
            return response()->error();
        } else {
            $response = BranchResource::collection($result);

            return $response;
        }
    }

    public function delete($id)
    {
        if ($this->branchService->isMainBranch($id)) 
        return response()->error(trans('rules.branch.delete_main_branch'));

        $result = $this->branchService->delete($id);

        return !$result ? response()->error() : response()->success();
    }
}
