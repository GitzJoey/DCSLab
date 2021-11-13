<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\UpdateUserPassword;
use App\Http\Requests\ProfileRequest;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        return $result == null ? response()->error():response()->success();
    }

    public function changePassword(ProfileRequest $profileRequest)
    {
        $usr = Auth::user();
        $updateActions = new UpdateUserPassword();
        $updateActions->update($usr, $profileRequest->validated());
    }
}
