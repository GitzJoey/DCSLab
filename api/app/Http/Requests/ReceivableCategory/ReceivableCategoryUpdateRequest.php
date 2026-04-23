<?php

namespace App\Http\Requests\ReceivableCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ReceivableCategoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $receivableCategory = $this->route('receivable_category');

        return $user->can('update', $receivableCategory);
    }

    public function prepareForValidation()
    {
        $receivableCategory = $this->route('receivable_category');

        $this->merge([
            'company_id' => $receivableCategory?->company_id,
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
        return trans('validation_attributes.receivable_category');
    }
}
