<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $user = Auth::user();
        $company = $this->route('company');

        $currentRouteMethod = $this->route()->getActionMethod();
        switch ($currentRouteMethod) {
            case 'viewAny':
                return $user->can('viewAny', Company::class);
            case 'view':
                return $user->can('view', Company::class);
            case 'create':
                return $user->can('create', Company::class);
            case 'update':
                return $user->can('update', Company::class);
            case 'delete':
                return $user->can('delete', Company::class);
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
