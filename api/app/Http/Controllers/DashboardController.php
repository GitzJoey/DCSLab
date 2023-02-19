<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\DashboardActions;
use Illuminate\Http\Request;
use Tightenco\Ziggy\Ziggy;

class DashboardController extends BaseController
{
    private $dashboardActions;

    public function __construct(DashboardActions $dashboardActions)
    {
        parent::__construct();

        $this->middleware('auth');

        $this->dashboardActions = $dashboardActions;
    }

    public function index()
    {
        return view('dashboard.midone');
    }

    public function userMenu(Request $request)
    {
        $menu = [];

        $useCache = $request->has('refresh') ? false : true;

        $menu = $this->dashboardActions->createUserMenu($useCache);

        return $menu;
    }

    public function userApi()
    {
        return response()->json(new Ziggy);
    }
}
