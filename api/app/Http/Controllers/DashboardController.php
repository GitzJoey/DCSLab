<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\DashboardActions;
use App\Http\Requests\FileUploadRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tightenco\Ziggy\Ziggy;

class DashboardController extends BaseController
{
    private $dashboardActions;

    public function __construct(DashboardActions $dashboardActions)
    {
        parent::__construct();

        $this->middleware('auth');

        $this->dashboardActions = $dashboardActions;
    }

    public function userMenu(Request $request)
    {
        $menu = [];

        $useCache = $request->has('refresh') ? false : true;

        $menu = $this->dashboardActions->createUserMenu($useCache);

        return $menu;
    }

    public function userApi()
    {
        return response()->json(new Ziggy());
    }

    public function userUpload(FileUploadRequest $fileUploadRequest)
    {
        $request = $fileUploadRequest->validated();

        $file = $request->file('file');
        $path = $file->store('uploads');

        $url = url(Storage::url($path));

        return response()->json(['url' => $url], 200);
    }
}
