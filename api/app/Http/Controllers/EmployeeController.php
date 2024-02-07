<?php

namespace App\Http\Controllers;

use App\Actions\Employee\EmployeeActions;
use App\Http\Requests\EmployeeRequest;
use App\Http\Resources\EmployeeResource;
use App\Models\Company;
use App\Models\Employee;
use Exception;

class EmployeeController extends BaseController
{
    private $employeeActions;

    public function __construct(EmployeeActions $employeeActions)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->employeeActions = $employeeActions;
    }

    public function store(EmployeeRequest $employeeRequest)
    {
        $request = $employeeRequest->validated();

        $company_id = $request['company_id'];

        $code = $request['code'];
        if ($code == config('dcslab.KEYWORDS.AUTO')) {
            do {
                $code = $this->employeeActions->generateUniqueCode();
            } while (! $this->employeeActions->isUniqueCode($code, $company_id));
        } else {
            if (! $this->employeeActions->isUniqueCode($code, $company_id)) {
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
            'status' => $request['status'],
        ];

        $accessBranchArr = [];
        if (! empty($request['arr_access_branch_id'])) {
            for ($i = 0; $i < count($request['arr_access_branch_id']); $i++) {
                if (! Company::find($company_id)->branches()->where('id', '=', $request['arr_access_branch_id'][$i])->exists()) {
                    return response()->error([
                        'arr_access_branch_id' => [trans('rules.valid_branch')],
                    ], 422);
                }

                array_push($accessBranchArr, [
                    'branch_id' => $request['arr_access_branch_id'][$i],
                ]);
            }
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->employeeActions->create(
                $employeeArr,
                $userArr,
                $profileArr,
                $accessBranchArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function readAny(EmployeeRequest $employeeRequest)
    {
        $request = $employeeRequest->validated();

        $search = $request['search'];
        $paginate = $request['paginate'];
        $page = array_key_exists('page', $request) ? abs($request['page']) : 1;
        $perPage = array_key_exists('per_page', $request) ? abs($request['per_page']) : 10;
        $useCache = array_key_exists('refresh', $request) ? boolval($request['refresh']) : true;

        $companyId = $request['company_id'];

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->employeeActions->readAny(
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
            $result = $this->employeeActions->read($employee);
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
                $code = $this->employeeActions->generateUniqueCode();
            } while (! $this->employeeActions->isUniqueCode($code, $company_id, $employee->id));
        } else {
            if (! $this->employeeActions->isUniqueCode($code, $company_id, $employee->id)) {
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

        $accessBranchArr = [];
        if (! empty($request['arr_access_branch_id'])) {
            for ($i = 0; $i < count($request['arr_access_branch_id']); $i++) {
                if (! Company::find($company_id)->branches()->where('id', '=', $request['arr_access_branch_id'][$i])->exists()) {
                    return response()->error([
                        'arr_access_branch_id' => [trans('rules.valid_branch')],
                    ], 422);
                }

                array_push($accessBranchArr, [
                    'branch_id' => $request['arr_access_branch_id'][$i],
                ]);
            }
        }

        $result = null;
        $errorMsg = '';

        try {
            $result = $this->employeeActions->update(
                $employee,
                $employeeArr,
                $userArr,
                $profileArr,
                $accessBranchArr
            );
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return is_null($result) ? response()->error($errorMsg) : response()->success();
    }

    public function delete(Employee $employee, EmployeeRequest $employeeRequest)
    {
        $result = false;
        $errorMsg = '';

        try {
            $result = $this->employeeActions->delete($employee);
        } catch (Exception $e) {
            $errorMsg = app()->environment('production') ? '' : $e->getMessage();
        }

        return ! $result ? response()->error($errorMsg) : response()->success();
    }
}
