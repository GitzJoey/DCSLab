<?php

namespace App\Http\Requests\StockAdjustmentInProductSerial;

use App\Models\StockAdjustmentInProductSerial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentInProductSerialUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $stockAdjustmentInProductSerial = $this->route('stock_adjustment_in_product_serial');

        return $user->can('update', StockAdjustmentInProductSerial::class, $stockAdjustmentInProductSerial) ? true : false;
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
            'serial' => trans('validation_attributes.stock_adjustment_in_product_serial.serial'),
        ];
    }
}
