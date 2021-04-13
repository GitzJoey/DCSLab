<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('dashboard.index');
        //or
        // return view('dashboard.index_2');
    }

    public function profile()
    {
        return view('dashboard.profile');
    }

    public function settings()
    {
        return view('dashboard.settings');
    }
}
