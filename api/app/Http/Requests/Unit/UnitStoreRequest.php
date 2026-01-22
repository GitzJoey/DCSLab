<?php

namespace App\Http\Requests\Unit;

use App\Enums\UnitTypeEnum;
use App\Helpers\HashidsHelper;
use App\Models\Unit;
use App\Rules\IsValidCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UnitStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (! Auth::check()) {
            return false;
        }

        /** @var \App\User */
        $user = Auth::user();

        return $user->can('create', Unit::class) ? true : false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'company_id' => ['required', 'integer', 'bail', new IsValidCompany()],
            'code' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::enum(UnitTypeEnum::class)],
        ];
    }

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.unit.company'),
            'code' => trans('validation_attributes.unit.code'),
            'name' => trans('validation_attributes.unit.name'),
            'description' => trans('validation_attributes.unit.description'),
            'type' => trans('validation_attributes.unit.type'),
        ];
    }

    public function prepareForValidation()
    {
        if ($this->has('company_id')) {
            $this->merge([
                'company_id' => HashidsHelper::decodeId($this->company_id),
            ]);
        }
    }
}
