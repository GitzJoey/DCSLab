<?php

namespace App\Http\Requests\PurchaseAdditionalCostCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class PurchaseAdditionalCostCategoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $purchaseAdditionalCostCategory = $this->route('pacc');

        return $user->can('update', $purchaseAdditionalCostCategory);
    }

    public function prepareForValidation()
    {
    }

    public function rules()
    {
        return [
            'code' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return trans('validation_attributes.purchase_additional_cost_category');
    }
}
