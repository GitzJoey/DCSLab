<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends BaseController
{
    private $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        parent::__construct();

        $this->middleware('auth');

        $this->dashboardService = $dashboardService;
    }

    public function index(Request $request)
    {
        return view('dashboard.midone');
    }

    public function userMenu(Request $request)
    {
        $menu = [];

        $useCache = $request->has('refresh') ? false:true;

        $menu = $this->dashboardService->createMenu($useCache);

        return $menu;
    }
}
