<?php

namespace App\Http\Controllers;

use App\Actions\RandomGenerator;
use Illuminate\Http\Request;

class CommonController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
    }

    public function getCountries()
    {
        return [
            ['name' => 'Indonesia', 'code' => 'ID'],
            ['name' => 'Singapore', 'code' => 'SG']            
        ];
    }

    public function getStatus()
    {
        return [
            ['name' => 'components.dropdown.values.statusDDL.active', 'code' => '1'],
            ['name' => 'components.dropdown.values.statusDDL.inactive', 'code' => '0'],
        ];
    }


    public function getRandGenerator(Request $request)
    {

    }
}
