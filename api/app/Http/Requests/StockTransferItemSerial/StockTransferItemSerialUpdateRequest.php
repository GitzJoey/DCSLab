<?php

namespace App\Http\Requests\StockTransferItemSerial;

use App\Models\StockTransferItemSerial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StockTransferItemSerialUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $stockTransferItemSerial = $this->route('stock_transfer_item_serial');

        return $user->can('update', StockTransferItemSerial::class, $stockTransferItemSerial) ? true : false;
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
            'serial' => trans('validation_attributes.stock_transfer_item_serial.serial'),
        ];
    }
}
