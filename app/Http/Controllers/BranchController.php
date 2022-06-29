<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\BranchService;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Requests\BranchRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Exception;

class BranchController extends BaseController
{
    private $branchService;
    
    public function __construct(BranchService $branchService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->branchService = $branchService;
    }

    public function list(BranchRequest $branchRequest)
    {
        $request = $branchRequest->validated();
        
        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $companyId = $request['company_id'];

        $result = $this->branchService->list(
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

    public function read(Branch $branch, BranchRequest $branchRequest)
    {
        $request = $branchRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->branchService->read($branch);
        } catch(Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }
        
        if (is_null($result)) {
            return response()->error($errorMsg);
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
        
        $branchArr = $request;

        $company_id = $request['company_id'];
        $code = $request['code'];

        if ($code == config('dcslab.KEYWORDS.AUTO')) {
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

        $branchArr['code'] = $code;
        $is_main = $request['is_main'];

        $result = null;
        $errorMsg = '';

        try {
            if ($is_main) $this->branchService->resetMainBranch($company_id);
            $result = $this->branchService->create(
                $branchArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function update(Branch $branch, BranchRequest $branchRequest)
    {
        $request = $branchRequest->validated();

        $branchArr = $request;

        $company_id = $request['company_id'];
        $code = $request['code'];

        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->branchService->generateUniqueCode($company_id);
            } while (!$this->branchService->isUniqueCode($code, $company_id, $branch->id));
        } else {
            if (!$this->branchService->isUniqueCode($code, $company_id, $branch->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $branchArr['code'] = $code;
        $is_main = $request['is_main'];

        $result = null;
        $errorMsg = '';

        try {
            if ($is_main) $this->branchService->resetMainBranch($company_id);
            $result = $this->branchService->update(
                $branch,
                $branchArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function resetMainBranch(Request $request)
    {
        if (!$request->has('companyId')) return response()->error();
    
        $result = $this->branchService->resetMainBranch(Hashids::decode($request['companyId'])[0]);
    
        if (is_null($result)) {
            return response()->error();
        } else {
            $response = BranchResource::collection($result);

            return $response;
        }
    }

    public function delete(Branch $branch)
    {
        if ($branch->is_main) 
            return response()->error(trans('rules.branch.delete_main_branch'));

        $result = false;
        $errorMsg = '';

        try {
            $result = $this->branchService->delete($branch);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return !$result ? response()->error($errorMsg) : response()->success();
    }
}
