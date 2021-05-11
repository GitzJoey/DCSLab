<?php

namespace App\Http\Controllers;

use App\Services\ProductProductService;
use Illuminate\Http\Request;

use Vinkla\Hashids\Facades\Hashids;

class ProductProductController extends Controller
{
    private $productProductService;

    public function __construct(ProductProductService $productProductService)
    {
        $this->middleware('auth');
        $this->productProductService = $productProductService;
    }

    public function index()
    {
        $test = $this->read();

        return view('product.products.index', compact('test'));
    }

    public function read()
    {
        return $this->productProductService->read();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:255',
            'group_id' => 'required|max:255',
            'brand_id' => 'required|max:255',
            'name' => 'required|max:255',
            'unit_id' => 'required|max:255',
            'price' => 'required|max:255',
            'tax' => 'required|max:255',
            'information' => 'required|max:255',
            'estimated_capital_price' => 'required|max:255',
            'is_use_serial' => 'required',
            'is_buy' => 'required',
            'is_production_material' => 'required',
            'is_production_result' => 'required',
            'is_sell' => 'required',
            'is_active' => 'required'
        ]);

        $rolePermissions = [];
        for($i = 0; $i < count($request['permissions']); $i++) {
            array_push($rolePermissions, array (
                'id' => Hashids::decode($request['permissions'][$i])[0]
            ));
        }

        $result = $this->productProductService->create(
            $request['code'],
            $request['group_id'],
            $request['brand_id'],
            $request['name'],
            $request['unit_id'],
            $request['price'],
            $request['tax'],
            $request['information'],
            $request['estimated_capital_price'],
            $request['is_use_serial'],
            $request['is_buy'],
            $request['is_production_material'],
            $request['is_production_result'],
            $request['is_sell'],
            $request['is_active'],
            $rolePermissions
        );

        if ($result == 0) {
            return response()->json([
                'message' => ''
            ],500);
        } else {
            return response()->json([
                'message' => ''
            ],200);
        }
    }

    public function update($id, Request $request)
    {
        $inputtedRolePermissions = [];
        for ($i = 0; $i < count($request['permissions']); $i++) {
            array_push($inputtedRolePermissions, array(
                'id' => Hashids::decode($request['permissions'][$i])[0]
            ));
        }

        $result = $this->productProductService->update(
            $id,
            $request['code'],
            $request['group_id'],
            $request['brand_id'],
            $request['name'],
            $request['unit_id'],
            $request['price'],
            $request['tax'],
            $request['information'],
            $request['estimated_capital_price'],
            $request['is_use_serial'],
            $request['is_buy'],
            $request['is_production_material'],
            $request['is_production_result'],
            $request['is_sell'],
            $request['is_active'],
            $inputtedRolePermissions
        );

        return response()->json();
    }

    public function delete($id)
    {
        $this->productProductService->delete($id);

        return response()->json();
    }
}
