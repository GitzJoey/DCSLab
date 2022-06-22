<?php

namespace App\Http\Requests;

use App\Enums\ActiveStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //Authorization Error
        //return false;

        if (!Auth::check()) return false;

        /** @var \App\User */
        $user = Auth::user();

        if ($this->route()->getActionMethod() == 'read' && $user->can('view', $user, User::class)) return true;
        if ($this->route()->getActionMethod() == 'store' && $user->can('create', $user, User::class)) return true;
        if ($this->route()->getActionMethod() == 'update' && $user->can('update', $user, User::class)) return true;
        if ($this->route()->getActionMethod() == 'delete' && $user->can('delete', $user, User::class)) return true;

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
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'address' => 'nullable',
            'city' => 'nullable',
            'postal_code' => 'nullable',
            'img_path' => 'nullable',
            'remarks' => 'nullable',
            'theme' => 'nullable',
            'dateFormat' => 'nullable',
            'timeFormat' => 'nullable',
        ];

        if ($this->route()->getActionMethod() == 'read') {
            $rules_read = [
                'search' => ['present', 'string'],
                'paginate' => ['required', 'boolean'],
                'page' => ['required_if:paginate,true', 'numeric'],
                'perPage' => ['required_if:paginate,true', 'numeric'],
                'refresh' => ['nullable', 'boolean']
            ];
            return $rules_read;
        }
        else if ($this->route()->getActionMethod() == 'store') {
            $rules_store = [
                //Testing Server Request Validation Error
                //'name' => 'min:1000',
                //'email' => 'min:1000',
                'name' => 'required|alpha_num',
                'email' => 'required|email|max:255|unique:users',
                'roles' => 'required',
                'tax_id' => 'required',
                'ic_num' => 'required',
                'status' => [new Enum(ActiveStatus::class)],
                'country' => 'required',
            ];

            return array_merge($rules_store, $nullableArr);
        } else if ($this->route()->getActionMethod() == 'update') {
            $id = $this->route()->parameter('id');
            $rules_update = [
                'name' => 'required|alpha_num',
                'email' => 'required|email|max:255|unique:users,email,'.$id,
                'roles' => 'required',
                'tax_id' => 'required',
                'ic_num' => 'required',
                'status' => [new Enum(ActiveStatus::class)],
                'country' => 'required'
            ];

            return array_merge($rules_update, $nullableArr);
        } else {
            return [
                '' => 'required'
            ];
        }
    }

    public function attributes()
    {
        return [
            'name' => trans('validation_attributes.name'),
            'email' => trans('validation_attributes.email'),
            'roles' => trans('validation_attributes.roles'),
            'tax_id' => trans('validation_attributes.tax_id'),
            'ic_num' => trans('validation_attributes.ic_num'),
            'status' => trans('validation_attributes.status'),
            'country' => trans('validation_attributes.country')
        ];
    }

    public function validationData()
    {
        $additionalArray = [];

        return array_merge($this->all(), $additionalArray);
    }

    public function prepareForValidation()
    {
        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'read':
                $this->merge([
                    'paginate' => $this->has('paginate') ? filter_var($this->paginate, FILTER_VALIDATE_BOOLEAN) : true,
                ]);
                break;
            case 'store':
            case 'update':
                $this->merge([
                    'status' => ActiveStatus::isValid($this->status) ? ActiveStatus::fromName($this->status)->value : -1
                ]);
                break;
            default:
                $this->merge([]);
                break;
        }

    }
}
