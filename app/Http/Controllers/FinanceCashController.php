<?php

namespace App\Http\Controllers;

use App\Services\FinanceCashService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class FinanceCashController extends Controller
{
    private $financeCashService;

    public function __construct(FinanceCashService $financeCashService)
    {
        $this->middleware('auth');
        $this->financeCashService = $financeCashService;
    }

    public function index()
    {
        $test = $this->read();

        return view('finance.cashs.index', compact('test'));
    }

    public function read()
    {
        return $this->financeCashService->read();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'name' => 'required|max:255',
            'is_bank' => 'required',
            'is_active' => 'required'
        ]);

        $rolePermissions = [];
        for($i = 0; $i < count($request['permissions']); $i++) {
            array_push($rolePermissions, array (
                'id' => Hashids::decode($request['permissions'][$i])[0]
            ));
        }

        $result = $this->financeCashService->create(
            $request['code'],
            $request['name'],
            $request['is_bank'],
            $request['is_active'],
            $rolePermissions
        );

        if ($result == 0) {
            return response()->json([
                'message' => ''
            ],500);
        } else {
            return response()->json([
                'message' => ''
            ],200);
        }
    }

    public function update($id, Request $request)
    {
        $inputtedRolePermissions = [];
        for ($i = 0; $i < count($request['permissions']); $i++) {
            array_push($inputtedRolePermissions, array(
                'id' => Hashids::decode($request['permissions'][$i])[0]
            ));
        }

        $result = $this->financeCashService->update(
            $id,
            $request['code'],
            $request['name'],
            $request['is_bank'],
            $request['is_active'],
            $inputtedRolePermissions
        );

        return response()->json();
    }

    public function delete($id)
    {
        $this->financeCashService->delete($id);

        return response()->json();
    }
}
