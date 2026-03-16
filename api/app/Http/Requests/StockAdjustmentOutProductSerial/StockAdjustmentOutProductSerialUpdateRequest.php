<?php

namespace App\Http\Requests\StockAdjustmentOutProductSerial;

use App\Models\StockAdjustmentOutProductSerial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentOutProductSerialUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $stockAdjustmentOutProductSerial = $this->route('stock_adjustment_out_product_serial');

        return $user->can('update', StockAdjustmentOutProductSerial::class, $stockAdjustmentOutProductSerial) ? true : false;
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
            'serial' => trans('validation_attributes.stock_adjustment_out_product_serial.serial'),
        ];
    }
}
