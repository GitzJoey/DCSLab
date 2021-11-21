<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\UpdateUserPassword;
use App\Http\Requests\ProfileRequest;
use App\Services\UserService;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;

class ProfileController extends BaseController
{
    private $userService;

    public function __construct(UserService $userService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->userService = $userService;
    }

    public function readProfile()
    {
        return $this->userService->readBy('ID', Auth::id());
    }

    public function updateProfile(ProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();
        $user = Auth::user();

        $profile = array (
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'postal_code' => $request['postal_code'],
            'country' => $request['country'],
            'tax_id' => $request['tax_id'],
            'ic_num' => $request['ic_num'],
            'remarks' => $request['remarks'],
        );

        $result = $this->userService->updateProfile($user, $profile, true);

        return is_null($result) ? response()->error():response()->success();
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

        return is_null($result) ? response()->error():response()->success();
    }

    public function updateRoles(ProfileRequest $profileRequest)
    {
        $request = $profileRequest->validated();

        $usr = Auth::user();

        $rolesId = [];

        $result = $this->userService->updateRoles($usr, $rolesId, true);

        return is_null($result) ? response()->error():response()->success();
    }

    public function sendEmailVerification()
    {
        $usr = Auth::user();

        $usr->sendEmailVerificationNotification();

        return response()->success();
    }

    public function emailVerification(Request $request, $id, $hash)
    {
        if (!URL::hasValidSignature($request)) {
            return redirect('/');
        }

        $usr = $this->userService->readBy('ID', $id);

        $usr->markEmailAsVerified();

        return redirect('/home');
    }
}
