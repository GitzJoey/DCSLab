<?php

namespace App\Http\Controllers;

use App\Services\CompanyCompanyService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class CompanyCompanyController extends Controller
{
    private $companyCompanyService;

    public function __construct(CompanyCompanyService $companyCompanyService)
    {
        $this->middleware('auth');
        $this->companyCompanyService = $companyCompanyService;
    }

    public function index()
    {
        $test = $this->read();

        return view('company.companies.index', compact('test'));
    }

    public function read()
    {
        return $this->companyCompanyService->read();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'name' => 'required|max:255',
            'is_active' => 'required'
        ]);

        $rolePermissions = [];
        for($i = 0; $i < count($request['permissions']); $i++) {
            array_push($rolePermissions, array (
                'id' => Hashids::decode($request['permissions'][$i])[0]
            ));
        }

        $result = $this->companyCompanyService->create(
            $request['code'],
            $request['name'],
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

        $result = $this->companyCompanyService->update(
            $id,
            $request['code'],
            $request['name'],
            $request['is_active'],
            $inputtedRolePermissions
        );

        return response()->json();
    }

    public function delete($id)
    {
        $this->companyCompanyService->delete($id);

        return response()->json();
    }
}