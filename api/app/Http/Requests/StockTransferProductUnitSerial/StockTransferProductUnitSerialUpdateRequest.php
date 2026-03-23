<?php

namespace App\Http\Requests\StockTransferProductUnitSerial;

use App\Models\StockTransferProductUnitSerial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockTransferProductUnitSerialUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $stockTransferProductUnitSerial = $this->route('stock_transfer_product_unit_serial');

        return $user->can('update', StockTransferProductUnitSerial::class, $stockTransferProductUnitSerial) ? true : false;
    }

    public function rules()
    {
        return [
            'serial' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'serial' => trans('validation_attributes.stock_transfer_product_unit_serial.serial'),
        ];
    }
}
