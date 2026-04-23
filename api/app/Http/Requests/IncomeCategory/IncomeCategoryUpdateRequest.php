<?php

namespace App\Http\Requests\IncomeCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class IncomeCategoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $incomeCategory = $this->route('income_category');

        return $user->can('update', $incomeCategory);
    }

    public function prepareForValidation()
    {
        $incomeCategory = $this->route('income_category');

        $this->merge([
            'company_id' => $incomeCategory?->company_id,
        ]);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer'],
            'code' => ['required', 'string'],
            'name' => ['required', 'string', 'max:255'],
            'sequence' => ['required', 'integer', 'min:0'],
        ];
    }

    public function attributes()
    {
        return trans('validation_attributes.income_category');
    }
}
