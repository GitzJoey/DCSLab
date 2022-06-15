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

    public function getRouteActivity(Request $request)
    {
        $act = $this->activityLogService->getAuthUserActivities();

        $result = collect();

        $pos = '';
        foreach($act as $a) {
            if ($a->log_name == 'RoutingActivity') {
                $pos = $this->togglePos($pos);

                $result->add(array(
                    'pos' => $pos,
                    'dot' => true,
                    'log_name' => $a->log_name,
                    'title' => 'Routing',
                    'description' => $a->description,
                    'timestamp' => $a->created_at->format('Y.m.d H.i.s'),
                    'data' => []
                ));
            } else if ($a->log_name == 'AuthActivity') {
                $pos = $this->togglePos($pos);

                $result->add(array(
                    'pos' => $pos,
                    'dot' => true,
                    'log_name' => $a->log_name,
                    'title' => $a->description,
                    'description' => $a->description,
                    'timestamp' => $a->created_at->format('Y.m.d H.i.s'),
                    'data' => []
                ));
            } else { }
        }

        return $result;
    }

    private function togglePos($pos)
    {
        if ($pos == '') {
            $newpos = 'left';
        } else if ($pos == 'left') {
            $newpos = 'right';
        } else if ($pos == 'right') {
            $newpos = 'left';
        } else { }

        return $newpos;
    }
}
