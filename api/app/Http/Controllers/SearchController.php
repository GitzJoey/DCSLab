<?php

namespace App\Http\Controllers;

use App\Actions\Branch\BranchActions;
use App\Actions\Company\CompanyActions;
use App\Actions\User\UserActions;
use App\Http\Requests\SearchRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SearchController extends BaseController
{
    private UserActions $userActions;

    private CompanyActions $companyActions;

    private BranchActions $branchActions;

    public function __construct(
        UserActions $userActions,
        CompanyActions $companyActions,
        BranchActions $branchActions
    ) {
        parent::__construct();

        $this->userActions = $userActions;
        $this->companyActions = $companyActions;
        $this->branchActions = $branchActions;
    }

    public function search(SearchRequest $searchRequest): JsonResponse
    {
        $errorMsg = '';
        $request = $searchRequest->validated();
        $response = null;

        try {
            $response = '';
        } catch (Exception $e) {
            $errorMsg = app()->isProduction() ? '' : $e->getMessage();
        }

        if (! empty($errorMsg)) {
            return $this->apiResponse($errorMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->apiResponse($response, Response::HTTP_OK);
    }
}
