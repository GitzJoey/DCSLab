<?php

namespace App\Http\Controllers;

use App\Actions\User\UserActions;
use App\Http\Resources\UserResource;
use App\Rules\InactiveUser;
use App\Rules\MaxTokens;
use App\Rules\MustResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class ApiAuthController extends Controller
{
    private $userActions;

    public function __construct(UserActions $userActions)
    {
        $this->userActions = $userActions;
    }

    public function auth(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $user = $this->userActions->readBy('EMAIL', $request['email']);

        if ($user && Hash::check($request->password, $user->password)) {
            $request->validate([
                'email' => [new InactiveUser($user), new MaxTokens($user->email)],
                'password' => [new MustResetPassword($user)],
            ]);

            $token = $user->createToken(Config::get('dcslab.API_TOKEN_NAME'))->plainTextToken;

            return (new UserResource($user))->additional(['tokens' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]]);
        }

        return response()->error();
    }
}
