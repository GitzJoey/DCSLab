<?php

namespace App\Http\Controllers;


class DevController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
    }
}
