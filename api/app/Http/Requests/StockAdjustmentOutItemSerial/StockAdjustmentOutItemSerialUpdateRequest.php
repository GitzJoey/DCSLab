<?php

namespace App\Http\Requests\StockAdjustmentOutItemSerial;

use App\Models\StockAdjustmentOutItemSerial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentOutItemSerialUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $stockAdjustmentOutItemSerial = $this->route('stock_adjustment_out_item_serial');

        return $user->can('update', StockAdjustmentOutItemSerial::class, $stockAdjustmentOutItemSerial) ? true : false;
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
            'serial' => trans('validation_attributes.stock_adjustment_out_item_serial.serial'),
        ];
    }
}
