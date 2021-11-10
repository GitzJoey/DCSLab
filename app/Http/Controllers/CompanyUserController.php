<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ActivityLogService;
use App\Services\CompanyUserService;

use Vinkla\Hashids\Facades\Hashids;


class CompanyUserController extends BaseController
{
    private $companyUserService;

    public function __construct(CompanyUserService $companyUserService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->companyUserService = $companyUserService;
        $this->activityLogService = $activityLogService;
    }
}
