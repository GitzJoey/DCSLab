<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Services\UserService;

use Illuminate\Support\Facades\Config;

class ApiAuthController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function auth(AuthRequest $authRequest)
    {
        $request = $authRequest->validated();

        $user = $this->userService->readBy('EMAIL', $request['email']);

        $token = $user->createToken(Config::get('const.DEFAULT.API_TOKEN_NAME'))->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function signup(AuthRequest $authRequest)
    {
        $request = $authRequest->validated();

        $user = $this->userService->register($request['name'], $request['email'], $request['password'], '');

        $token = $user->createToken(Config::get('const.DEFAULT.API_TOKEN_NAME'))->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
