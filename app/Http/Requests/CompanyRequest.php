<?php

namespace App\Http\Requests;

use App\Enums\ActiveStatus;
use App\Enums\UserRoles;
use App\Rules\deactivateDefaultCompany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rules\Enum;
use Vinkla\Hashids\Facades\Hashids;

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

        if (!Auth::check()) return false;
        if (empty(Auth::user()->roles)) return false;

        if (Auth::user()->hasRole(UserRoles::DEVELOPER->value)) return true;

        if ($this->route()->getActionMethod() == 'store' && !Auth::user()->hasPermission('create-company')) return false;
        if ($this->route()->getActionMethod() == 'update' && !Auth::user()->hasPermission('update-company')) return false;

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $companyId = $this->has('company_id') ? Hashids::decode($this['company_id'])[0]:null;

        $nullableArr = [
            'address' => 'nullable',
        ];

        $userId = Auth::id();

        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'store':
                $rules_store = [
                    'code' => ['required', 'max:255'],
                    'name' => 'required|max:255',
                    'default' => 'required|boolean',
                    'status' => [new Enum(ActiveStatus::class), new deactivateDefaultCompany($this->input('default'), $this->input('status'))]
                ];
                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', 'bail'],
                    'code' => ['required', 'max:255'],
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

    public function attributes()
    {
        return [
            'company_id' => trans('validation_attributes.company'),
        ];
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
