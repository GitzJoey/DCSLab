<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends BaseController
{
    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->activityLogService = $activityLogService;
    }

    public function logRouteActivity(Request $request)
    {
        $to = $request->get('to');
        $params = $request->get('params');

        $this->activityLogService->RoutingActivity($to, $params);
    }
}
