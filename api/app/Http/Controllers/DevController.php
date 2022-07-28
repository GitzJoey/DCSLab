<?php

namespace App\Http\Controllers;

use App\Http\Resources\SupplierResource;
use Illuminate\Http\Request;

class DevController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
    }
}
