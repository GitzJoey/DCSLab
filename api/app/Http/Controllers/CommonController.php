<?php

namespace App\Http\Controllers;

use App\Enums\RecordStatus;
use App\Enums\UnitCategory;
use App\Enums\PaymentTermType;
use App\Enums\ProductGroupCategory;

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
            ['name' => 'components.dropdown.values.statusDDL.deleted', 'code' => RecordStatus::DELETED->name],
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
    
    public function getPaymentTermType()
    {
        return [
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.pia', 'code' => PaymentTermType::PAYMENT_IN_ADVANCE->name],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.net', 'code' => PaymentTermType::X_DAYS_AFTER_INVOICE->name],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.eom', 'code' => PaymentTermType::END_OF_MONTH->name],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.cod', 'code' => PaymentTermType::CASH_ON_DELIVERY->name],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.cnd', 'code' => PaymentTermType::CASH_ON_NEXT_DELIVERY->name],
        ];
    }
}
