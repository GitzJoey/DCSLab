<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Services\RoleService;
use App\Services\UserService;
use App\Services\EmployeeService;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Resources\RoleResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;

class EmployeeController extends BaseController
{
    private $employeeService;
    private $userService;
    private $roleService;
    
    public function __construct(EmployeeService $employeeService, RoleService $roleService, UserService $userService,)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->employeeService = $employeeService;
        $this->userService = $userService;
        $this->roleService = $roleService;
    }

    public function read(EmployeeRequest $employeeRequest)
    {
        $request = $employeeRequest->validated();
        
        $search = $request->has('search') && !is_null($request['search']) ? $request['search']:'';
        $search = !is_null($search) ? $search : '';

        $paginate = $request->has('paginate') ? $request['paginate']:true;
        $paginate = !is_null($paginate) ? $paginate : true;
        $paginate = is_numeric($paginate) ? abs($paginate) : true;

        $page = $request->has('page') ? $request['page']:1;
        $page = !is_null($page) ? $page : 1;
        $page = is_numeric($page) ? abs($page) : 1; 

        $perPage = $request->has('perPage') ? $request['perPage']:10;
        $perPage = !is_null($perPage) ? $perPage : 10;
        $perPage = is_numeric($perPage) ? abs($perPage) : 10;  

        $companyId = Hashids::decode($request['companyId'])[0];

        $result = $this->employeeService->read(
            companyId: $companyId,
            search: $search,
            paginate: $paginate,
            page: $page,
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

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('const.DEFAULT.KEYWORDS.AUTO')) {
            do {
                $code = $this->employeeService->generateUniqueCode($company_id);
            } while (!$this->employeeService->isUniqueCode($code, $company_id));
        } else {
            if (!$this->employeeService->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $name = $request['name'];
        $email = $request['email'];
        $address = $request['address'];
        $city = $request['city'];
        $postal_code = $request['postal_code'];
        $country = $request['country'];
        $tax_id = $request['tax_id'];
        $ic_num = $request['ic_num'];
        $img_path = $request['img_path'];
        $join_date = $request['join_date'];
        $remarks = $request['remarks'];
        $status = $request['status'];

        if (!empty($img_path)) {
            $image = $request['img_path'];
            $filename = time().".".$image->getClientOriginalExtension();
            
            $file = $image->storePubliclyAs('usr', $filename, 'public');
            $profile['img_path'] = $file;
        }

        $result = $this->employeeService->create(
            company_id: $company_id,
            code: $code, 
            name: $name,
            email: $email,
            address: $address,
            city: $city,
            postal_code: $postal_code,
            country: $country,
            tax_id: $tax_id,
            ic_num: $ic_num,
            img_path: $img_path,
            join_date: $join_date,
            remarks: $remarks,
            status: $status
        );
        return is_null($result) ? response()->error():response()->success();
    }

    public function update($id, EmployeeRequest $employeeRequest)
    {
        $request = $employeeRequest->validated();
        
        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('const.DEFAULT.KEYWORDS.AUTO')) {
            do {
                $code = $this->employeeService->generateUniqueCode($company_id);
            } while (!$this->employeeService->isUniqueCode($code, $company_id, $id));
        } else {
            if (!$this->employeeService->isUniqueCode($code, $company_id, $id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }
        $name = $request['name'];
        $email = $request['email'];
        $address = $request['address'];
        $city = $request['city'];
        $postal_code = $request['postal_code'];
        $country = $request['country'];
        $tax_id = $request['tax_id'];
        $ic_num = $request['ic_num'];
        $img_path = $request['img_path'];
        $remarks = $request['remarks'];
        $status = $request['status'];

        $result = $this->employeeService->update(
            id: $id,
            code: $code, 
            name: $name,
            email: $email,
            address: $address,
            city: $city,
            postal_code: $postal_code,
            country: $country,
            tax_id: $tax_id,
            ic_num: $ic_num,
            img_path: $img_path,
            join_date: null,
            remarks: $remarks,
            status: $status
        );
        return is_null($result) ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $userId = Auth::id();
        
        $result = $this->employeeService->delete($id);

        return !$result ? response()->error():response()->success();
    }
}