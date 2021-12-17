<?php

namespace App\Http\Controllers;

use App\Services\BrandService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Vinkla\Hashids\Facades\Hashids;

class BrandController extends BaseController
{
    private $brandService;

    public function __construct(BrandService $brandService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->brandService = $brandService;
    }

    public function read(Request $request)
    {
        $search = $request->has('search') && !is_null($request['search']) ? $request['search']:'';
        $paginate = $request->has('paginate') ? boolVal($request['paginate']):true;
        $perPage = $request->has('perPage') ? $request['perPage']:null;

        $companyId = Hashids::decode($request['companyId'])[0];

        return $this->brandService->read($companyId, $search, $paginate, $perPage);
    }
}
