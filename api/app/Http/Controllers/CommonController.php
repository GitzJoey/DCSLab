<?php

namespace App\Http\Controllers;

use App\Enums\RecordStatus;
use App\Enums\PaymentTermType;

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
