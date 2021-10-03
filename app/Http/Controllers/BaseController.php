<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class BaseController extends Controller
{
    public function __construct()
    {

    }

    public function hasSelectedCompanyOrCompany()
    {
        $result = true;
        if (empty(session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY')))) {
            if (auth()->user()->companies()->count() == 0) {
                $result = false;
            }
        }
        return $result;
    }
}
