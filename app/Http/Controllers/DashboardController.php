<?php

namespace App\Http\Controllers;

use App\Rules\sameEmail;
use App\Services\ActivityLogService;
use App\Services\UserService;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private $userService;
    private $roleService;
    private $activityLogService;

    public function __construct(UserService $userService, RoleService $roleService, ActivityLogService $activityLogService)
    {
        $this->middleware('auth');
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        $roles = $this->roleService->getRolesByUserId(Auth::user()->id);

        return view('dashboard.index', compact($roles, 'roles'));
    }

    public function profile(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('dashboard.profile');
    }

    public function inbox(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('dashboard.inbox');
    }

    public function readProfile()
    {
        $id = Auth::user()->id;

        if (!$id) {
            return response()->json([
                'message' => ''
            ],500);
        }

        return $this->userService->getUserById($id);
    }

    public function updateProfile(Request $request)
    {
        $id = Auth::user()->id;

        $request->validate([
            'name' => 'required',
            'email' => new sameEmail($id),
            'tax_id' => 'required',
            'ic_num' => 'required',
        ]);

        $profile = array (
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'postal_code' => $request['postal_code'],
            'tax_id' => $request['tax_id'],
            'ic_num' => $request['ic_num'],
            'remarks' => $request['remarks'],
        );

        if ($request->hasFile('img_path')) {
            $image = $request->file('img_path');
            $filename = time().".".$image->getClientOriginalExtension();

            $file = $image->storePubliclyAs('usr', $filename, 'public');
            $profile['img_path'] = $file;
        }

        $retval = $this->userService->updateProfile(
            $id,
            $profile
        );

        if ($retval == 0) {
            return response()->json([
                'message' => ''
            ],500);
        } else {
            return response()->json([
                'message' => ''
            ],200);
        }
    }

    public function activateSystem($system)
    {
        $usrId = Auth::user()->id;
        switch(strtoupper($system))
        {
            case 'POS':
                $this->roleService->setRoleForUserId('pos-owner', $usrId);
                break;
            case 'WHS':
                $this->roleService->setRoleForUserId('whs-owner', $usrId);
                break;
            default:
                break;
        }

        return redirect()->action([DashboardController::class, 'index']);
    }
}
