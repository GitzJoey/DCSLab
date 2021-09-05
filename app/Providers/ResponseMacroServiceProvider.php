<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class ResponseMacroServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Response::macro('success', function ($data = null) {
            return Response::json([
                'errors'  => false,
                'data' => $data == null ? '':$data,
            ]);
        });

        Response::macro('error', function ($message = '', $status = 400) {
            return Response::json([
                'errors'  => true,
                'message' => $message,
            ], $status);
        });
    }
}
