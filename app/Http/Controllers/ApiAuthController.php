<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Rules\inactiveUser;
use App\Rules\maxTokens;
use App\Rules\mustResetPassword;
use App\Services\UserService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ApiAuthController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function auth(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email']
        ]);

        $user = $this->userService->readBy('EMAIL', $request['email']);
            
        if ($user && Hash::check($request->password, $user->password)) {
            $request->validate([
                'email' => [new inactiveUser($user), new maxTokens($user->email)],
                'password' => [new mustResetPassword($user)] 
            ]);

            $token = $user->createToken(Config::get('const.DEFAULT.API_TOKEN_NAME'))->plainTextToken;

            return (new UserResource($user))->additional(['tokens' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]]);
        } 

        return response()->error();
    }

    public function signup(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'alpha_num', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', new Password(8), 'confirmed']
        ]);

        try {
            $user = $this->userService->register($request['name'], $request['email'], $request['password'], '');

            $token = $user->createToken(Config::get('const.DEFAULT.API_TOKEN_NAME'))->plainTextToken;

            return (new UserResource($user))->additional(['tokens' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]]);
        } catch (Exception $e) {
            return response()->error();
        }
    }
}
