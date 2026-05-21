<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        //Authorization Error
        //return false;

        if (! Auth::check()) {
            return false;
        }

        $authUser = Auth::user();
        $user = $this->route('user');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'viewAny':
                return $authUser->can('viewAny', User::class);
            case 'view':
                return $authUser->can('view', User::class);
            case 'create':
                return $authUser->can('create', User::class);
            case 'update':
                return $authUser->can('update', User::class);
            case 'delete':
                return false;
            default:
                return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
