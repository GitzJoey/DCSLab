<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Company;
use App\Models\Employee;
use App\Services\EmployeeService;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;

class EmployeeController extends BaseController
{
    private $employeeService;

    public function __construct(EmployeeService $employeeService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->employeeService = $employeeService;
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
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $employeeArr = [
            'company_id' => $company_id,
            'code' => $code,
            'join_date' => $request['join_date'],
            'status' => $request['status'],
        ];

        $userArr = [
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => 'testing',
        ];

        $first_name = '';
        $last_name = '';
        if ($request['name'] == trim($request['name']) && strpos($request['name'], ' ') !== false) {
            $pieces = explode(' ', $request['name']);
            $first_name = $pieces[0];
            $last_name = $pieces[1];
        } else {
            $first_name = $request['name'];
        }

        $profileArr = [
            'first_name' => $first_name,
            'last_name' => $last_name,
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
                array_push($accessesArr, [
                    'branch_id' => $request['accessBranchIds'][$i],
                ]);
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

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function list(EmployeeRequest $employeeRequest)
    {
        $request = $employeeRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('perPage', $request) ? abs($request['perPage']) : 10;
        $useCache = array_key_exists('refresh', $request) ? boolval($request['refresh']) : true;

        $companyId = $request['company_id'];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->employeeService->list(
                companyId: $companyId,
                search: $search,
                paginate: $paginate,
                page: $page,
                perPage: $perPage,
                useCache: $useCache
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
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
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        if (is_null($result)) {
            return response()->error($errorMsg);
        } else {
            $response = new EmployeeResource($result);

            return $response;
        }
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
                    'code' => [trans('rules.unique_code')],
                ], 422);
            }
        }

        $employeeArr = [
            'code' => $code,
            'status' => $request['status'],
        ];

        $userArr = [
            'name' => $request['name'],
        ];

        $first_name = '';
        $last_name = '';
        if ($request['name'] == trim($request['name']) && strpos($request['name'], ' ') !== false) {
            $pieces = explode(' ', $request['name']);
            $first_name = $pieces[0];
            $last_name = $pieces[1];
        } else {
            $first_name = $request['name'];
        }

        $profileArr = [
            'first_name' => $first_name,
            'last_name' => $last_name,
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
                array_push($accessesArr, [
                    'branch_id' => $request['accessBranchIds'][$i],
                ]);
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
