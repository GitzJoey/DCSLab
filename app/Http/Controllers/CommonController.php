<?php

namespace App\Http\Controllers;

use App\Enums\ProductGroupCategory;
use App\Enums\RecordStatus;
use App\Enums\UnitCategory;
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

    public function getProductGroupCategory()
    {
        return [
            ['name' => 'components.dropdown.values.productGroupCategoryDDL.product', 'code' => ProductGroupCategory::PRODUCTS->name],
            ['name' => 'components.dropdown.values.productGroupCategoryDDL.service', 'code' => ProductGroupCategory::SERVICES->name],
            ['name' => 'components.dropdown.values.productGroupCategoryDDL.product_and_service', 'code' => ProductGroupCategory::PRODUCTS_AND_SERVICES->name],
        ];
    }

    public function getUnitCategory()
    {
        return [
            ['name' => 'components.dropdown.values.unitCategoryDDL.product', 'code' => UnitCategory::PRODUCTS->name],
            ['name' => 'components.dropdown.values.unitCategoryDDL.service', 'code' => UnitCategory::SERVICES->name],
            ['name' => 'components.dropdown.values.unitCategoryDDL.product_and_service', 'code' => UnitCategory::PRODUCTS_AND_SERVICES->name],
        ];
    }
}