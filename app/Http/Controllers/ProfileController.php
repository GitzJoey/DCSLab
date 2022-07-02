<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\UpdateUserPassword;
use App\Http\Requests\ProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class ProfileController extends BaseController
{
    private $userService;
    private $roleService;

    public function __construct(UserService $userService, RoleService $roleService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->userService = $userService;
        $this->roleService = $roleService;
    }

    public function readAuth()
    {
        $result = $this->userService->readBy('ID', Auth::id());
        
        if (is_null($result)) {
            return response()->error();
        } else {
            $response = new UserResource($result);

            return $response;
        }
    }

    public function updateProfile(ProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();
        $user = Auth::user();

        $profile = [
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'postal_code' => $request['postal_code'],
            'country' => $request['country'],
            'tax_id' => $request['tax_id'],
            'ic_num' => $request['ic_num'],
            'remarks' => $request['remarks'],
        ];

        $result = $this->userService->updateProfile($user, $profile, true);

        return !$result ? response()->error() : response()->success();
    }

    public function changePassword(ProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();

        $usr = Auth::user();
        $updateActions = new UpdateUserPassword();
        $updateActions->update($usr, $request);
    }

    public function updateSettings(ProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();

        $usr = Auth::user();
        $settings = [
            'PREFS.THEME' => $request['theme'],
            'PREFS.DATE_FORMAT' => $request['dateFormat'],
            'PREFS.TIME_FORMAT' => $request['timeFormat'],
        ];

        $result = $this->userService->updateSettings($usr, $settings, true);

        if (array_key_exists('apiToken', $request))
            $this->userService->resetTokens($usr->id);

        return !$result ? response()->error() : response()->success();
    }

    public function updateRoles(ProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();

        $usr = Auth::user();

        $roles = $request['roles'];
        $rolesId = $usr->roles->pluck('id');

        if ($roles === 'pos')
            $rolesId->push($this->roleService->readBy('NAME', 'POS-owner')->id);

        $result = $this->userService->updateRoles($usr, $rolesId->toArray(), true);

        return is_null($result) ? response()->error() : response()->success();
    }

    public function sendEmailVerification()
    {
        /** @var \App\User */
        $usr = Auth::user();

        $usr->sendEmailVerificationNotification();

        return response()->success();
    }
}
