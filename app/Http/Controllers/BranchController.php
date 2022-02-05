<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BranchService;
use Vinkla\Hashids\Facades\Hashids;
use App\Http\Requests\BranchRequest;
use App\Http\Resources\BranchResource;

class BranchController extends Controller
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
        $search = $request->has('search') && !is_null($request['search']) ? $request['search']:'';
        $paginate = true;
        $perPage = $request->has('perPage') ? $request['perPage']:null;

        $companyId = Hashids::decode($request['companyId'])[0];

        $result = $this->branchService->read($companyId, $search, $paginate, $perPage);

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

        return $result ? response()->error():response()->success();
    }
}
