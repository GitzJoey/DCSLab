<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Company;
use App\Models\ChartOfAccount;
use App\Enums\AccountType;
use App\Services\ChartOfAccountService;
use App\Http\Requests\ChartOfAccountRequest;
use App\Http\Resources\ChartOfAccountResource;

class ChartOfAccountController extends BaseController
{
    private $chartOfAccountService;

    public function __construct(ChartOfAccountService $chartOfAccountService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->chartOfAccountService = $chartOfAccountService;
    }

    public function store(ChartOfAccountRequest $chartOfAccountRequest)
    {
        $request = $chartOfAccountRequest->validated();

        $company_id = $request['company_id'];

        $parent_id = $request['parent_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $loopCount = 0;
            do {
                $loopCount = $loopCount + 1;
    
                $code = $this->chartOfAccountService->generateUniqueCode($company_id, $parent_id) + $loopCount;
            } while (!$this->chartOfAccountService->isUniqueCode($parent_id, str_pad($code, 2, '0', STR_PAD_LEFT), $company_id));
            $code = str_pad($code, 2, '0', STR_PAD_LEFT);
        } else {
            if (!$this->chartOfAccountService->isUniqueCode($parent_id, $code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $chartOfAccountArr = [
            'company_id' => $request['company_id'],
            'parent_id' => $request['parent_id'],
            'code' => $code,
            'name' => $request['name'],
            'account_type' => $request['account_type'],
            'remarks' => $request['remarks'],
        ];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->chartOfAccountService->create($chartOfAccountArr);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function list(ChartOfAccountRequest $chartOfAccountRequest)
    {
        $request = $chartOfAccountRequest->validated();

        $companyId = $request['company_id'];
        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;
        $useCache = array_key_exists('refresh', $request) ? boolval($request['refresh']) : true;

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->chartOfAccountService->list(
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
            $response = ChartOfAccountResource::collection($result);

            return $response;
        }
    }

    public function read(ChartOfAccount $chartOfAccount, ChartOfAccountRequest $chartOfAccountRequest)
    {
        $request = $chartOfAccountRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->chartOfAccountService->read($chartOfAccount);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new ChartOfAccountResource($result);

            return $response;
        }
    }

    public function update(ChartOfAccount $chartOfAccount, ChartOfAccountRequest $chartOfAccountRequest)
    {
        $request = $chartOfAccountRequest->validated();

        $company_id = $request['company_id'];

        $parent_id = $request['parent_id'];
        
        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            $loopCount = 0;
            do {
                $loopCount = $loopCount + 1;
    
                $code = $this->chartOfAccountService->generateUniqueCode($company_id, $parent_id) + $loopCount;
            } while (!$this->chartOfAccountService->isUniqueCode($parent_id, str_pad($code, 2, '0', STR_PAD_LEFT), $company_id, $chartOfAccount->id));
            $code = str_pad($code, 2, '0', STR_PAD_LEFT);
        } else {
            if (!$this->chartOfAccountService->isUniqueCode($parent_id, $code, $company_id, $chartOfAccount->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $chartOfAccountArr = [
            'company_id' => $request['company_id'],
            'parent_id' => $request['parent_id'],
            'code' => $code,
            'name' => $request['name'],
            'account_type' => $request['account_type'],
            'remarks' => $request['remarks'],
        ];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->chartOfAccountService->update(
                $chartOfAccount,
                $chartOfAccountArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(ChartOfAccount $chartOfAccount)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->chartOfAccountService->delete($chartOfAccount);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return !$result ? response()->error($errorMsg) : response()->success();
    }
}
