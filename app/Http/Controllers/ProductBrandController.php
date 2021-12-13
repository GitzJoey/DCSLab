<?php

namespace App\Http\Controllers;

use App\Models\ProductBrand;
use App\Rules\uniqueCode;
use App\Services\ActivityLogService;
use App\Services\ProductBrandService;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
use App\Actions\RandomGenerator;

class ProductBrandController extends BaseController
{
    private $productBrandService;
    private $activityLogService;

    public function __construct(ProductBrandService $productBrandService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->productBrandService = $productBrandService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('product.brands.index');
    }

    public function read()
    {
        return $this->productBrandService->read();
    }

    public function getAllProductBrand()
    {
        return $this->productBrandService->getAllProductBrand();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => ['required', 'min:1', 'max:255', new uniqueCode('create', '', 'productbrands')],
            'name' => 'required|max:255'
        ]);

        $randomGenerator = new randomGenerator();
        $code = $request['code'];
        if ($code == 'AUTO') {
            $code_count = 1;
            do {
                $code = $randomGenerator->generateOne(99999999);
                $code_count = ProductBrand::where('code', $code)->count();
            }
            while ($code_count != 0);
        };

        $result = $this->productBrandService->create(
            Hashids::decode($request['company_id'])[0],
            $code,
            $request['name']
        );
        
        return $result == 0 ? response()->error():response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'productbrands')],
            'name' => 'required|max:255',
        ]);

        $result = $this->productBrandService->update(
            $id,
            Hashids::decode($request['company_id'])[0],
            $request['code'],
            $request['name'],
        );
        return $result == 0 ? response()->error():response()->success();
    }

    public function delete($id)
    {
        $result = $this->productBrandService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}
