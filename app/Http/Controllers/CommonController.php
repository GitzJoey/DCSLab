<?php

namespace App\Http\Controllers;

use App\Actions\RandomGenerator;
use App\Enums\ActiveStatus;
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
            ['name' => 'Singapore', 'code' => 'SG'],
        ];
    }

    public function getStatus()
    {
        return [
            ['name' => 'components.dropdown.values.statusDDL.active', 'code' => ActiveStatus::ACTIVE->name],
            ['name' => 'components.dropdown.values.statusDDL.inactive', 'code' => ActiveStatus::INACTIVE->name],
        ];
    }

    public function getMainStatus()
    {
        return [
            ['name' => 'YES', 'code' => 1],
            ['name' => 'NO', 'code' => 0],
        ];
    }

    public function getRandGenerator(Request $request)
    {

    }
}
