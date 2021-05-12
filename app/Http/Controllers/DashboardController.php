<?php

namespace App\Http\Controllers;

use App\Rules\sameEmail;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->middleware('auth');
        $this->userService = $userService;
    }

    public function index()
    {
        return view('dashboard.index');
    }

    public function profile()
    {
        return view('dashboard.profile');
    }

    public function inbox()
    {
        return view('dashboard.inbox');
    }

    public function activity()
    {
        return view('dashboard.activity');
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
}
