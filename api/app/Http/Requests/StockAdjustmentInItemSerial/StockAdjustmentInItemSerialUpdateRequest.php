<?php

namespace App\Http\Requests\StockAdjustmentInItemSerial;

use App\Models\StockAdjustmentInItemSerial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentInItemSerialUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $stockAdjustmentInItemSerial = $this->route('stock_adjustment_in_item_serial');

        return $user->can('update', StockAdjustmentInItemSerial::class, $stockAdjustmentInItemSerial) ? true : false;
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
            'serial' => trans('validation_attributes.stock_adjustment_in_item_serial.serial'),
        ];
    }
}
