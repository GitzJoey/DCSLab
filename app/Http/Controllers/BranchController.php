<?php

namespace App\Http\Controllers;

use App\Services\BranchService;

use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Requests\BranchRequest;
use App\Http\Resources\BranchResource;

class BranchController extends BaseController
{
    private $branchService;
    
    public function __construct(BranchService $branchService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->branchService = $branchService;
    }

    public function read(Request $request)
    {       
        $companyId = $request->has('companyId') ? Hashids::decode($request['companyId'])[0] : null;

        $search = $request->has('search') ? $request['search'] : '';
        $search = is_null($search) == false ? $search : '';

        $paginate = $request->has('paginate') ? $request['paginate'] : true;
        $paginate = is_null($paginate) == false ? $paginate : true;
        $paginate = is_numeric($paginate) == true ? $paginate : true;
        $paginate = $paginate == 0 && $paginate == 1 ? $paginate : true;

        $perPage = $request->has('perPage') ? $request['perPage'] : 10;    
        $perPage = is_null($perPage) == false ? $perPage : 10;
        $perPage = is_numeric($perPage) == true ? $perPage : 10;  
        $perPage = $perPage > 0 ? $perPage : 10;

        $result = $this->branchService->read(
            companyId: $companyId,
            search: $search,
            paginate: $paginate,
            perPage: $perPage
        );

        if (is_null($result)) {
            return response()->error();
        } else {
            $response = BranchResource::collection($result);

            return $response;
        }
    }

    public function store(BranchRequest $branchRequest)
    {   
        $request = $branchRequest->validated();
        
        $company_id = Hashids::decode($request['company_id'])[0];
        $code = $request['code'];
        $name = $request['name'];
        $address = $request['address'];
        $city = $request['city'];
        $contact = $request['contact'];
        $remarks = $request['remarks'];
        $status = $request['status'];

        $result = $this->branchService->create(
            $company_id,
            $code, 
            $name,
            $address,
            $city,
            $contact,
            $remarks,
            $status,
        );

        return is_null($result) ? response()->error():response()->success();
    }

    public function update($id, BranchRequest $branchRequest)
    {
        $request = $branchRequest->validated();

        $company_id = Hashids::decode($request['company_id'])[0];
        $code = $request['code'];
        $name = $request['name'];
        $address = $request['address'];
        $city = $request['city'];
        $contact = $request['contact'];
        $remarks = $request['remarks'];
        $status = $request['status'];

        $branch = $this->branchService->update(
            $id,
            $company_id,
            $code, 
            $name,
            $address,
            $city,
            $contact,
            $remarks,
            $status,
        );

        return is_null($branch) ? response()->error() : response()->success();
    }

    public function delete($id)
    {
        $result = $this->branchService->delete($id);

        return is_null($result) ? response()->error():response()->success();
    }
}
