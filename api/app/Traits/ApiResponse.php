<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;

trait ApiResponse
{
    /**
     * @param  mixed  $content  Data payload, error message, collection, or resource.
     * @param  int  $status  HTTP status code.
     */
    protected function apiResponse(mixed $content = null, int $status = 200): JsonResponse
    {
        // Handle Error Status Codes (4xx and 5xx)
        if ($status >= 400) {
            return $this->handleErrorResponse($content, $status);
        }

        //  2. Handle Success Status Codes (2xx)
        return $this->handleSuccessResponse($content, $status);
    }

    /**
     * Process success payloads dynamically based on their type.
     */
    private function handleSuccessResponse(mixed $data, int $status): JsonResponse
    {
        // If it's already null, return empty or your structural null format
        if ($data === null) {
            return response()->json(null, $status);
        }

        // If it's an Eloquent Paginator, let it handle its own structure natively
        if ($data instanceof AbstractPaginator) {
            return response()->json($data, $status);
        }

        // If it's a Laravel API Resource or Resource Collection, resolve it
        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            return response()->json($data->response()->getData(true), $status);
        }

        // For arrays, strings, or standard objects, wrap them in your unified 'data' key
        return response()->json(['data' => $data], $status);
    }

    /**
     * Process error payloads dynamically based on the status code and type.
     */
    private function handleErrorResponse(mixed $message, int $status): JsonResponse
    {
        // Handle Validation Errors (422)
        if ($status === 422) {
            if (is_array($message)) {
                // If it's already wrapped in an 'errors' key, use it as-is
                if (array_key_first($message) === 'errors') {
                    return response()->json($message, 422);
                }

                // Otherwise, wrap the array under 'errors'
                return response()->json(['errors' => $message], 422);
            }

            // If a single string error message is sent for 422, map it cleanly
            return response()->json(['errors' => ['generic' => [$message]]], 422);
        }

        // Handle Server Crashes / Critical issues (500)
        if ($status === 500) {
            return response()->json([
                'message' => is_array($message) ? implode(' ', $message) : $message,
            ], 500);
        }

        // Fallback for other client errors (400, 401, 403, 404)
        return response()->json([
            'message' => is_array($message) ? ($message['message'] ?? implode(' ', $message)) : $message,
        ], $status);
    }
}
