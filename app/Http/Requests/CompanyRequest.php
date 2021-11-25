<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

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
        $currentRouteMethod = $this->route()->getActionMethod();
        switch($currentRouteMethod) {
            case 'store':
                return [
                    'code' => ['required', 'max:255'],
                    'name' => 'required|max:255',
                    'status' => 'required'
                ];
            case 'update':
                return [
                    'code' => 'required',
                    'name' => 'required|max:255',
                    'status' => 'required'
                ];
            default:
                return [
                    //
                ];
        }
    }
}
