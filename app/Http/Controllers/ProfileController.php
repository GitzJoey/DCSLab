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

    public function updateProfile(ProfileRequest $request)
    {
        $usr = Auth::user();
        $updateActions = new UpdateUserPassword();
        $updateActions->update($usr, $request->validated());
    }

    public function changePassword(ProfileRequest $request)
    {


    }
}
