<?php

namespace App\Http\Controllers;

use App\Http\Requests\BranchRequest;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Models\Company;
use App\Services\BranchService;
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

    public function store(BranchRequest $branchRequest)
    {
        $request = $branchRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->branchService->generateUniqueCode();
            } while (! $this->branchService->isUniqueCode($code, $company_id));
        } else {
            if (! $this->branchService->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $branchArr = [
            'company_id' => $company_id,
            'code' => $code,
            'name' => $request['name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'contact' => $request['contact'],
            'is_main' => $request['is_main'],
            'remarks' => $request['remarks'],
            'status' => $request['status'],
        ];

        $result = null;
        $errorMsg = '';

        try {
            if ($branchArr['is_main']) {
                $this->branchService->resetMainBranch(companyId: $company_id);
            }

            $result = $this->branchService->create($branchArr);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function list(BranchRequest $branchRequest)
    {
        $request = $branchRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;
        $useCache = array_key_exists('refresh', $request) ? boolval($request['refresh']) : true;

        $companyId = $request['company_id'];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->branchService->list(
                companyId: $companyId,
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
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new BranchResource($result);

            return $response;
        }
    }

    public function getBranchByCompany(Company $company)
    {
        $result = null;
        $errorMsg = '';

        try {
            $result = $this->branchService->getBranchByCompany(company: $company);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = BranchResource::collection($result);

            return $response;
        }
    }

    public function getMainBranchByCompany(Company $company)
    {
        $result = null;
        $errorMsg = '';

        try {
            $result = $this->branchService->getMainBranchByCompany(company: $company);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            return $result;
        }
    }

    public function update(Branch $branch, BranchRequest $branchRequest)
    {
        $request = $branchRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->branchService->generateUniqueCode();
            } while (! $this->branchService->isUniqueCode($code, $company_id, $branch->id));
        } else {
            if (! $this->branchService->isUniqueCode($code, $company_id, $branch->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $branchArr = [
            'code' => $code,
            'name' => $request['name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'contact' => $request['contact'],
            'is_main' => $request['is_main'],
            'remarks' => $request['remarks'],
            'status' => $request['status'],
        ];

        $result = null;
        $errorMsg = '';

        try {
            if ($branchArr['is_main']) {
                $this->branchService->resetMainBranch(companyId: $company_id);
            }

            $result = $this->branchService->update(
                $branch,
                $branchArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Branch $branch)
    {
        if ($branch->is_main) {
            return response()->error(trans('rules.branch.delete_main_branch'));
        }

        $result = false;
        $errorMsg = '';

        try {
            $result = $this->branchService->delete($branch);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
