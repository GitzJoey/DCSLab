<?php

namespace App\Http\Requests;

use App\Enums\ActiveStatus;
use App\Rules\uniqueCode;
use App\Rules\deactivateDefaultCompany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rules\Enum;

class CompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $nullableArr = [
            'address' => 'nullable',
        ];

        $userId = Auth::id();

        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'store':
                $rules_store = [
                    'code' => ['required', 'max:255', new uniqueCode(table: 'companies', userId: $userId)],
                    'name' => 'required|max:255',
                    'default' => 'required|boolean',
                    'status' => [new Enum(ActiveStatus::class), new deactivateDefaultCompany($this->input('default'), $this->input('status'))]
                ];
                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'code' => ['required', 'max:255', new uniqueCode(table: 'companies', userId: $userId)],
                    'name' => 'required|max:255',
                    'default' => 'required|boolean',
                    'status' => [new Enum(ActiveStatus::class), new deactivateDefaultCompany($this->input('default'), $this->input('status'))]
                ];
                return array_merge($rules_update, $nullableArr);
            default:
                return [
                    '' => 'required'
                ];
        }
    }

    public function validationData()
    {
        $additionalArray = [];

        return array_merge($this->all(), $additionalArray);
    }

    public function prepareForValidation()
    {
        $this->merge([
            'default' => $this->has('default') ? filter_var($this->default, FILTER_VALIDATE_BOOLEAN) : false,
            'status' => ActiveStatus::isValid($this->status) ? ActiveStatus::fromName($this->status)->value : -1
        ]);
    }
}
