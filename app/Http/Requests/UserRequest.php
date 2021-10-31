<?php

namespace App\Http\Requests;

use App\Rules\validDropDownValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UserRequest extends FormRequest
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
        if ($this->route()->getActionMethod() == 'store') {
            return [
                'name' => 'required|alpha',
                'email' => 'required|email|max:255|unique:users',
                'roles' => 'required',
                'tax_id' => 'required',
                'ic_num' => 'required',
                'status' => 'required',
                'country' => 'required',
            ];
        } else if ($this->route()->getActionMethod() == 'update') {
            $id = $this->route()->parameter('id');
            return [
                'name' => 'required|alpha',
                'email' => 'required|email|max:255|unique:users,email,'.$id,
                'roles' => 'required',
                'tax_id' => 'required',
                'ic_num' => 'required',
                'status' => new validDropDownValue('ACTIVE_STATUS'),
                'country' => 'required'
            ];
        } else {
            return [

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
}
