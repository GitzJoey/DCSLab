<?php

namespace App\Http\Controllers;

use App\Actions\Branch\BranchActions;
use App\Http\Requests\BranchRequest;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Models\Company;
use Exception;

class BranchController extends BaseController
{
    private $branchActions;

    public function __construct(BranchActions $branchActions)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->branchActions = $branchActions;
    }

    public function store(BranchRequest $branchRequest)
    {
        $request = $branchRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->branchActions->generateUniqueCode();
            } while (! $this->branchActions->isUniqueCode($code, $company_id));
        } else {
            if (! $this->branchActions->isUniqueCode($code, $company_id)) {
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
                $this->branchActions->resetMainBranch(companyId: $company_id);
            }

            $result = $this->branchActions->create($branchArr);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(BranchRequest $branchRequest)
    {
        $request = $branchRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('per_page', $request) ? abs($request['per_page']) : 10;
        $useCache = array_key_exists('refresh', $request) ? boolval($request['refresh']) : true;

        $companyId = $request['company_id'];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->branchActions->readAny(
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
            $result = $this->branchActions->read($branch);
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
            $result = $this->branchActions->getBranchByCompany(company: $company);
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
            $result = $this->branchActions->getMainBranchByCompany(company: $company);
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
                $code = $this->branchActions->generateUniqueCode();
            } while (! $this->branchActions->isUniqueCode($code, $company_id, $branch->id));
        } else {
            if (! $this->branchActions->isUniqueCode($code, $company_id, $branch->id)) {
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
                $this->branchActions->resetMainBranch(companyId: $company_id);
            }

            $result = $this->branchActions->update(
                $branch,
                $branchArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Branch $branch, BranchRequest $branchRequest)
    {
        $request = $branchRequest->validated();
        
        if ($branch->is_main) {
            return response()->error(trans('rules.branch.delete_main_branch'));
        }

        $result = false;
        $errorMsg = '';

        try {
            $result = $this->branchActions->delete($branch);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
