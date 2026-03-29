<?php

namespace App\Http\Requests\RepToPascalThis;

use App\Helpers\HashidsHelper;
use App\Rules\IsValidCompany;
use App\Rules\RepToPascalThisUpdateValidCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class RepToPascalThisUpdateRequest extends FormRequest
{
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();
        $RepToCamelThis = $this->route('RepToSnakeThis');

        return $user->can('update', $RepToCamelThis);
    }

    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'code' => ['required', 'string', 'max:255', new RepToPascalThisUpdateValidCode($this->company_id, $this->route('RepToSnakeThis'))],
            'remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.RepToSnakeThis.company'),
            'code' => trans('validation_attributes.RepToSnakeThis.code'),
            'remarks' => trans('validation_attributes.RepToSnakeThis.remarks'),
        ];
    }

    public function prepareForValidation()
    {
        $this->merge([
            'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
            'remarks' => $this->filled('remarks') ? $this['remarks'] : null,
        ]);
    }
}
