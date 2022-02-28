<?php

namespace App\Http\Controllers;

use App\Services\ProductGroupService;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Auth;

class ProductGroupController extends BaseController
{
    private $productGroupService;

    public function __construct(ProductGroupService $productGroupService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->productGroupService = $productGroupService;
    }

    public function read(Request $request)
    {        
        $search = $request->has('search') && !is_null($request['search']) ? $request['search']:'';
        $paginate = $request->has('paginate') ? boolVal($request['paginate']):true;
        $perPage = $request->has('perPage') ? $request['perPage']:10;

        $companyId = Hashids::decode($request['companyId'])[0];

        $category = '';

        return $this->productGroupService->read($companyId, $category, $search, $paginate, $perPage);
    }
}