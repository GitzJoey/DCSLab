<?php

namespace App\Http\Requests\LiabilityCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class LiabilityCategoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $liabilityCategory = $this->route('liability_category');

        return $user->can('update', $liabilityCategory);
    }

    public function prepareForValidation()
    {
        $liabilityCategory = $this->route('liability_category');

        $this->merge([
            'company_id' => $liabilityCategory?->company_id,
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
        return trans('validation_attributes.liability_category');
    }
}
