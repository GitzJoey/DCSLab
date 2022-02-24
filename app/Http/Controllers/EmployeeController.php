<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Services\RoleService;
use App\Services\UserService;
use App\Services\EmployeeService;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;

class EmployeeController extends BaseController
{
    private $employeeService;
    
    public function __construct(EmployeeService $employeeService, RoleService $roleService, UserService $userService,)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->employeeService = $employeeService;
        $this->roleService = $roleService;
        $this->userService = $userService;
    }

    public function read(Request $request)
    {
        $search = $request->has('search') && !is_null($request['search']) ? $request['search']:'';
        $paginate = $request->has('paginate') ? $request['paginate']:true;
        $perPage = $request->has('perPage') ? $request['perPage']:10;

        $companyId = Hashids::decode($request['companyId'])[0];
        // $userId = Hashids::decode($request['userId'])[0];

        $result = $this->employeeService->read(
            companyId: $companyId,
            // userId: $userId,
            search: $search,
            paginate: $paginate,
            perPage: $perPage
        );

        if (is_null($result)) {
            return response()->error();
        } else {
            $response = EmployeeResource::collection($result);

            return $response;
        }
    }

    public function store(EmployeeRequest $employeeRequest)
    {   
        $request = $employeeRequest->validated();
        
        $rolesId = [];
        array_push($rolesId, $this->roleService->getRoleByName('user')->id);

        $first_name = '';
        $last_name = '';
        if ($request['name'] == trim($request['name']) && strpos($request['name'], ' ') !== false) {
            $pieces = explode(" ", $request['name']);
            $first_name = $pieces[0];
            $last_name = $pieces[1];
        } else {
            $first_name = $request['name'];
        }

        $profile = array (
            'first_name' => $first_name,
            'last_name' => $last_name,
            'address' => $request['address'],
            'city' => $request['city'],
            'postal_code' => $request['postal_code'],
            'country' => $request['country'],
            'tax_id' => $request['tax_id'],
            'ic_num' => $request['ic_num'],
            'status' => $request['status'],
            'remarks' => $request['remarks'],
        );

        $user = $this->userService->create(
            $request['name'],
            $request['email'],
            '',
            $rolesId,
            $profile
        );
        $user_id = Hashids::decode($user)[0];

        $result = $this->employeeService->create(
            Hashids::decode($request['company_id'])[0],
            $user_id
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, EmployeeRequest $employeeRequest)
    {
        $request = $employeeRequest->validated();

        $userId = Employee::find($id);
        $userId = $userId['user_id'];

        $rolesId = [];
        array_push($rolesId, $this->roleService->getRoleByName('user')->id);

        $first_name = '';
        $last_name = '';
        if ($request['name'] == trim($request['name']) && strpos($request['name'], ' ') !== false) {
            $pieces = explode(" ", $request['name']);
            $first_name = $pieces[0];
            $last_name = $pieces[1];
        } else {
            $first_name = $request['name'];
        }

        $profile = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'address' => $request['address'],
            'city' => $request['city'],
            'postal_code' => $request['postal_code'],
            'country' => $request['country'],
            'tax_id' => $request['tax_id'],
            'ic_num' => $request['ic_num'],
            'status' => $request['status'],
            'remarks' => $request['remarks'],
        );

        $settings = [
            'THEME.CODEBASE' => $request['theme'],
            'PREFS.DATE_FORMAT' => $request['dateFormat'],
            'PREFS.TIME_FORMAT' => $request['timeFormat'],
        ];
        
        $user = $this->userService->update(
            $userId,
            $request['name'],
            $rolesId,
            $profile,
            $settings
        );
        $user_id = Hashids::decode($user);

        $result = $this->employeeService->update(
            $id,
            Hashids::decode($request['company_id'])[0],
            $user_id[0]
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->employeeService->delete($id);

        return is_null($result) ? response()->error():response()->success();
    }
}
