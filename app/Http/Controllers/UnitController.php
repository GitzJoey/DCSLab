<?php

namespace App\Http\Controllers;

use App\Enums\UnitCategory;
use App\Http\Resources\UnitResource;
use App\Services\UnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Vinkla\Hashids\Facades\Hashids;

class UnitController extends BaseController
{
    private $unitService;

    public function __construct(UnitService $unitService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->unitService = $unitService;
    }

    public function read(Request $request)
    {
        $search = $request->has('search') && !is_null($request['search']) ? $request['search']:'';
        $paginate = $request->has('paginate') ? boolVal($request['paginate']):true;
        $perPage = $request->has('perPage') ? $request['perPage']:10;

        $companyId = Hashids::decode($request['companyId'])[0];
        $category = $request->has('category') ? intVal($request['category']):UnitCategory::PRODUCTS;
 
        $result = $this->unitService->read($companyId, $category, $search, $paginate, $perPage);
        
        if (is_null($result)) {
            return response()->error();
        } else {
            $response = UnitResource::collection($result);

            return $response;
        } 
    }

    public function getUnitCategory(Request $request)
    {

    }
}