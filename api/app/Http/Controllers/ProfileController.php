<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Http\Resources\UserResource;
use App\Actions\Role\RoleActions;
use App\Actions\User\UserActions;
use Illuminate\Support\Facades\Auth;

class ProfileController extends BaseController
{
    private $userActions;

    private $roleActions;

    public function __construct(UserActions $userActions, RoleActions $roleActions)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->userActions = $userActions;
        $this->roleActions = $roleActions;
    }

    public function read()
    {
        $result = $this->userActions->readBy('ID', Auth::id());

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

        $result = $this->userActions->updateProfile($user, $profile, true);

        return ! $result ? response()->error() : response()->success();
    }

    public function changePassword(ProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();

        $usr = Auth::user();

        $this->userActions->changePassword($usr, $request->password);
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

        $result = $this->userActions->updateSettings($usr, $settings, true);

        if (array_key_exists('apiToken', $request)) {
            $this->userActions->resetTokens($usr->id);
        }

        return ! $result ? response()->error() : response()->success();
    }

    public function updateRoles(ProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();

        $usr = Auth::user();

        $roles = $request['roles'];
        $rolesId = $usr->roles->pluck('id');

        if ($roles === 'pos') {
            $rolesId->push($this->roleActions->readBy('NAME', 'POS-owner')->id);
        }

        $result = $this->userActions->updateRoles($usr, $rolesId->toArray(), true);

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
