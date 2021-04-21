<?php

namespace App\Http\Controllers;

use Illuminate\Http\RoleService;
use Illuminate\Http\Request;

class ProductGroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('product.groups.index');
    }
}
