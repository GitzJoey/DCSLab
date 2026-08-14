<?php

namespace App\Http\Controllers;

use App\Enums\PaymentTermTypeEnum;
use App\Enums\RecordStatusEnum;
use App\Enums\RoundingTypeEnum;
use Illuminate\Http\Request;

class CommonController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getCountries()
    {
        return [
            ['name' => 'Indonesia', 'code' => 'ID'],
            ['name' => 'Singapore', 'code' => 'SG'],
        ];
    }

    public function getStatus(Request $request)
    {
        $statuses = [
            ['name' => 'components.dropdown.values.statusDDL.active', 'code' => RecordStatusEnum::ACTIVE->name],
            ['name' => 'components.dropdown.values.statusDDL.inactive', 'code' => RecordStatusEnum::INACTIVE->name],
        ];

        if ($request->boolean('show_deleted', true)) {
            $statuses[] = ['name' => 'components.dropdown.values.statusDDL.deleted', 'code' => RecordStatusEnum::DELETED->name];
        }

        return $statuses;
    }

    public function getPaymentTermTypes()
    {
        return [
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.pia', 'code' => PaymentTermTypeEnum::PAYMENT_IN_ADVANCE->name],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.net', 'code' => PaymentTermTypeEnum::X_DAYS_AFTER_INVOICE->name],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.eom', 'code' => PaymentTermTypeEnum::END_OF_MONTH->name],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.cod', 'code' => PaymentTermTypeEnum::CASH_ON_DELIVERY->name],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.cnd', 'code' => PaymentTermTypeEnum::CASH_ON_NEXT_DELIVERY->name],
            ['name' => 'components.dropdown.values.paymentTermTypeDDL.cbs', 'code' => PaymentTermTypeEnum::CASH_BEFORE_SHIPMENT->name],
        ];
    }

    public function getRoundingTypes()
    {
        return [
            ['name' => 'components.dropdown.values.roundingTypeDDL.up', 'code' => RoundingTypeEnum::UP->name],
            ['name' => 'components.dropdown.values.roundingTypeDDL.down', 'code' => RoundingTypeEnum::DOWN->name],
        ];
    }
}
