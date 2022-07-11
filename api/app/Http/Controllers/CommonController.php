<?php

namespace App\Http\Controllers;

use App\Enums\RecordStatus;
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
            ['name' => 'components.dropdown.values.statusDDL.active', 'code' => RecordStatus::ACTIVE->name],
            ['name' => 'components.dropdown.values.statusDDL.inactive', 'code' => RecordStatus::INACTIVE->name],
        ];
    }

    public function getConfirmationDialog()
    {
        return [
            ['name' => 'components.dropdown.values.yesNoDDL.yes', 'code' => 1],
            ['name' => 'components.dropdown.values.yesNoDDL.no', 'code' => 0],
        ];
    }
}
