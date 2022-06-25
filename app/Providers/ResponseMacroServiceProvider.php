<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class ResponseMacroServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Response::macro('success', function ($data = null) {
            return Response::json($data == null ? null : [
                'data' => $data
            ]);
        });

        Response::macro('error', function ($message = '', $status = 500) {
            switch ($status) {
                case 500:
                    if (is_array($message)) {
                        return Response::json([
                            'message' => implode(' ', $message)
                        ], $status);                        
                    } else {
                        return Response::json([
                            'message' => $message
                        ], $status);
                    }
                    break;
                case 422:
                    if (is_array($message)) {
                        if (array_key_first($message) != 'errors') {
                            return Response::json([
                                'errors' => $message
                            ], $status);
                        } else {
                            return Response::json($message, $status);
                        }
                    } else {
                        return Response::json([
                            'errors' => [
                                '' => $message
                            ]
                        ], $status);
                    }        
                    break;
                default:
                    break;
            }
        });
    }
}
