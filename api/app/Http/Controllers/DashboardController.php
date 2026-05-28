<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\DashboardActions;
use App\Http\Requests\FileUploadRequest;
use App\Http\Resources\FileUploadResource;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tighten\Ziggy\Ziggy;

class DashboardController extends BaseController
{
    private DashboardActions $dashboardActions;

    public function __construct(DashboardActions $dashboardActions)
    {
        parent::__construct();

        $this->dashboardActions = $dashboardActions;
    }

    public function userMenu(Request $request): JsonResponse
    {
        $menu = [];

        $useCache = $request->has('refresh') ? false : true;

        $menu = $this->dashboardActions->createUserMenu($useCache);

        return $this->apiResponse($menu, Response::HTTP_OK);
    }

    public function userApi(): JsonResponse
    {
        return $this->apiResponse(new Ziggy, Response::HTTP_OK);
    }

    public function userUpload(FileUploadRequest $fileUploadRequest): JsonResponse
    {
        $erroMsg = '';

        $request = $fileUploadRequest->validated();

        $data = [];
        $url = '';

        try {
            $file = $request['file'];
            $filename = Str::random(32).'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('uploads', $filename, 'public');

            $url = asset('storage/'.$path);

            $data = [
                'url' => $url,
            ];
        } catch (Exception $e) {
            $erroMsg = app()->isProduction() ? '' : $e->getMessage();
        }

        if (empty($url)) {
            return $this->apiResponse($erroMsg, Response::HTTP_INTERNAL_SERVER_ERROR);
        } else {
            $response = (new FileUploadResource($data));

            return $this->apiResponse($response, Response::HTTP_OK);
        }
    }
}
