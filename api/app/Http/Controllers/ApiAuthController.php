<?php

namespace App\Http\Controllers;

use App\Actions\User\UserActions;
use App\Http\Resources\UserResource;
use App\Rules\ApiAuth\InactiveUser;
use App\Rules\ApiAuth\MaxTokens;
use App\Rules\ApiAuth\MustResetPassword;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
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
                'email' => [new InactiveUser($user), new MaxTokens($user)],
                'password' => [new MustResetPassword($user)],
            ]);

            $token = $user->createToken(Config::get('dcslab.API_TOKEN_NAME'))->plainTextToken;

            return (new UserResource($user))->additional(['tokens' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]]);
        }

        return $this->apiResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
