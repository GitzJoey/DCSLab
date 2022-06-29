<?php

namespace App\Http\Controllers;

use App\Services\EmployeeService;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Services\CompanyService;
use Exception;

class EmployeeController extends BaseController
{
    private $employeeService;
    
    public function __construct(EmployeeService $employeeService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->employeeService = $employeeService;
    }

    public function list(EmployeeRequest $employeeRequest)
    {
        $request = $employeeRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;

        $companyId = $request['company_id'];

        $result = $this->employeeService->list(
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

    public function read(Employee $employee, EmployeeRequest $employeeRequest)
    {
        $request = $employeeRequest->validated();

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->employeeService->read($employee);
        } catch(Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }
        
        if (is_null($result)) {
            return response()->error($errorMsg);
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
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->employeeService->generateUniqueCode();
            } while (!$this->employeeService->isUniqueCode($code, $company_id));
        } else {
            if (!$this->employeeService->isUniqueCode($code, $company_id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $employeeArr =[
            'company_id' => $request['company_id'],
            'code' => $code,
            'join_date' => $request['join_date'],
            'status' => $request['status']
        ];

        $userArr = [
            'name' => $request['name'],
            'email' => $request['email']
        ];

        $profileArr = [
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'postal_code' => $request['postal_code'],
            'country' => $request['country'],
            'tax_id' => $request['tax_id'],
            'ic_num' => $request['ic_num'],
            'remarks' => $request['remarks'],
        ];

        $accessesArr = [];
        if (!empty($request['accessBranchIds'])) {
            for ($i = 0; $i < count($request['accessBranchIds']); $i++) {
                array_push($accessesArr, array(
                    'branch_id' => Hashids::decode($request['accessBranchIds'][$i])[0]
                ));
            }
        }

        $result = null;
        $errorMsg = ''; 

        try {
            $result = $this->employeeService->create(
                $employeeArr,
                $userArr,
                $profileArr,
                $accessesArr
            );    
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg):response()->success();
    }

    public function update(Employee $employee, EmployeeRequest $employeeRequest)
    {
        $request = $employeeRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->employeeService->generateUniqueCode();
            } while (!$this->employeeService->isUniqueCode($code, $company_id, $employee->id));
        } else {
            if (!$this->employeeService->isUniqueCode($code, $company_id, $employee->id)) {
                return response()->error([
                    'code' => [trans('rules.unique_code')]
                ], 422);
            }
        }

        $employeeArr = [
            'code' => $code,
            'join_date' => $request['join_date'],
            'status' => $request['status']
        ];

        $userArr = [
            'name' => $request['name'],
            'email' => $request['email']
        ];

        $profileArr = [
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'address' => $request['address'],
            'city' => $request['city'],
            'postal_code' => $request['postal_code'],
            'country' => $request['country'],
            'tax_id' => $request['tax_id'],
            'ic_num' => $request['ic_num'],
            'remarks' => $request['remarks'],
        ];

        $accessesArr = [];
        if (!empty($request['accessBranchIds'])) {
            for ($i = 0; $i < count($request['accessBranchIds']); $i++) {
                array_push($accessesArr, array(
                    'branch_id' => Hashids::decode($request['accessBranchIds'][$i])[0]
                ));
            }
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->employeeService->update(
                $employee,
                $employeeArr,
                $userArr,
                $profileArr,
                $accessesArr
            );    
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Employee $employee)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->employeeService->delete($employee);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return !$result ? response()->error($errorMsg) : response()->success();
    }
}