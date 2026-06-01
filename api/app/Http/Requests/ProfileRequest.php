<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ProfileRequest extends FormRequest
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

        $currentRouteMethod = $this->route()->getActionMethod();

        switch ($currentRouteMethod) {
            case 'updateUserProfile':
            case 'updatePersonalInformation':
            case 'updateAccountSettings':
            case 'updateUserRoles':
            case 'updatePassword':
            case 'updateTokens':
                return $user->can('update', Profile::class);
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
        return match ($this->route()?->getActionMethod()) {
            'updateUserProfile' => $this->updateUserProfileRules(),
            'updatePersonalInformation' => $this->updatePersonalInformationRules(),
            'updateAccountSettings' => $this->updateAccountSettingsRules(),
            'updateUserRoles' => $this->updateUserRolesRules(),
            'updatePassword' => $this->updatePasswordRules(),
            'updateTokens' => $this->updateTokensRules(),
            default => $this->defaultRules(),
        };
    }

    private function updateUserProfileRules(): array
    {
        return [
            'name' => ['required', 'alpha_num'],
        ];
    }

    private function updatePersonalInformationRules(): array
    {
        return [
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'address' => 'nullable',
            'city' => 'nullable',
            'postal_code' => ['alpha_dash', 'min:5', 'max:10'],
            'country' => 'nullable',
            'tax_id' => 'required',
            'ic_num' => 'required',
            'remarks' => 'nullable',
        ];
    }

    private function updateUserRolesRules(): array
    {
        return [
            'roles' => 'required',
        ];
    }

    private function updatePasswordRules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed'],
            'password_confirmation' => ['required'],
        ];
    }

    private function updateTokensRules(): array
    {
        return [
            'theme' => 'required',
            'date_format' => 'required',
            'time_format' => 'required',
        ];
    }

    private function updateAccountSettingsRules(): array
    {
        return [
            'theme' => 'required',
            'date_format' => 'required',
            'time_format' => 'required',
        ];
    }

    private function nullableFields(): array
    {
        return [
        ];
    }
}
