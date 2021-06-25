<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class DevController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function db_backup()
    {
        return view ('dev.db_backup');
    }

    public function ex1()
    {
        $t = Config::get('const.DROPDOWN');


        return view('dev.ex1');
    }

    public function ex2()
    {
        return view('dev.ex2');
    }

    public function ex3()
    {
        return view('dev.ex3');
    }

    public function ex4()
    {
        return view('dev.ex4');
    }

    public function ex5()
    {
        return view('dev.ex5');
    }

}
