<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StoreFrontController extends Controller
{
    public function __construct()
    {

    }

    public function index()
    {
        return view('storefront.index');
    }
}
