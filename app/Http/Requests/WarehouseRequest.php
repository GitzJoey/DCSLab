<?php

namespace App\Http\Requests;

use App\Enums\ActiveStatus;
use App\Enums\UserRoles;
use App\Rules\isValidCompany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Vinkla\Hashids\Facades\Hashids;

class WarehouseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (!Auth::check()) return false;

        /** @var \App\User */
        $user = Auth::user();

        if (empty($user->roles)) return false;

        if ($user->hasRole(UserRoles::DEVELOPER->value)) return true;

        if ($this->route()->getActionMethod() == 'store' && !$user->hasPermission('warehouse-create')) return false;
        if ($this->route()->getActionMethod() == 'update' && !$user->hasPermission('warehouse-update')) return false;

        return false;
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
            'city' => 'nullable',
            'contact' => 'nullable',
            'remarks' => 'nullable',
        ];

        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'store':
                $rules_store = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'branch_id' => ['required'],
                    'code' => ['required', 'max:255'],
                    'name' => 'required|max:255',
                    'status' => [new Enum(ActiveStatus::class)]
                ];
                return array_merge($rules_store, $nullableArr);
            case 'update':
                $rules_update = [
                    'company_id' => ['required', new isValidCompany(), 'bail'],
                    'branch_id' => ['required'],
                    'code' => ['required', 'max:255'],
                    'name' => 'required|max:255',
                    'status' => [new Enum(ActiveStatus::class)]
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
            'company_id' => $this->has('company_id') ? Hashids::decode($this['company_id'])[0]:'',
            'status' => ActiveStatus::isValid($this->status) ? ActiveStatus::fromName($this->status)->value : -1
        ]);
    }
}
