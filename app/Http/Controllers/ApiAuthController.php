<?php

namespace App\Http\Controllers;

use App\Rules\maxTokens;
use App\Services\UserService;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

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
            'email' => ['required','email','max:255', new maxTokens($request['email'], 2)],
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid login details'
            ], 401);
        }

        $user = $this->userService->readBy('EMAIL', $request['email']);

        $token = $user->createToken(Config::get('const.DEFAULT.API_TOKEN_NAME'))->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
