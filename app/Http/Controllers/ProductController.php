<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Services\ProductUnitService;

use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

use App\Actions\RandomGenerator;

class ProductController extends BaseController
{
    private $productService;
    private $productUnitService;
    private $activityLogService;

    public function __construct(ProductService $productService, ProductUnitService $productUnitService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->productService = $productService;
        $this->productUnitService = $productUnitService;
        $this->activityLogService = $activityLogService;
    }

    public function index_product(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('product.products.index');
    }

    public function index_service(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('product.services.index');
    }

    public function read()
    {
        $userId = Auth::user()->id;
        return $this->productService->read($userId);
    }

    public function read_product()
    {
        return $this->productService->read_product();
    }

    public function read_service()
    {
        return $this->productService->read_service();
    }

    public function store(Request $request)
    {   
        $request->validate([
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'products')],
            'name' => 'required|max:255',
            'status' => 'required',
        ]);

        $faker = \Faker\Factory::create('id_ID');
        if ($request->code == '[AUTO]') {
           $request->code = $faker->creditCardNumber();
        };

        $tampungsaya = (new RandomGenerator())->generateOne(999999);

        # Jika Product... Maka...Config:get('const.DEFAULT.PRODCUT_TUPE.RAW_MATERIAL,SERVICE)
        if ($request->product_type[0] !== '4') {
            $company_id = $request->company_id != null ? Hashids::decode($request->company_id)[0]:$company_id = null;          
            $code = $request->code;
            $group_id = Hashids::decode($request->group_id)[0];
            $brand_id = Hashids::decode($request->brand_id)[0];
            $name = $request->name;
            $tax_status = $request->tax_status;
            $supplier_id = $request->supplier_id != null ? Hashids::decode($request->supplier_id)[0]:$supplier_id = null;
            $remarks = $request->remarks;
            $point = $request->point;
            $is_use_serial = $request->is_use_serial;
            $product_type = $request->product_type;
            $status = $request->status;

            $product_units = [];
            $count_unit = count($request['unit_id']);
            for ($i = 0; $i < $count_unit; $i++) {
                $is_base = is_null($request['is_base'][$i]) ? 0 : 1;
                $is_primary_unit = is_null($request['is_primary_unit'][$i]) ? 0 : 1;

                array_push($product_units, array (
                    'code' => $request->product_unit_code[$i],
                    'company_id' => $request->company_id,
                    #'product_id' => Hashids::decode($product)[0],
                    'unit_id' => Hashids::decode($request['unit_id'][$i])[0],
                    'is_base' => $is_base,
                    'conv_value' => $request['conv_value'][$i],
                    'is_primary_unit' => $is_primary_unit,
                    'remarks' => $request['remarks']
                ));
            }

            $product = $this->productService->create(
                $company_id,
                $code, 
                $group_id,
                $brand_id,
                $name,
                $tax_status,
                $supplier_id,
                $remarks,
                $point,
                $is_use_serial,
                $product_type,
                $status,
                $product_units
            );
    
            if ($product == 0) {
                return response()->error();
            };
    
            // $product_units = [];
            // $count_unit = count($request['unit_id']);
            // for ($i = 0; $i < $count_unit; $i++) {
            //     $is_base = is_null($request['is_base'][$i]) ? 0 : 1;
            //     $is_primary_unit = is_null($request['is_primary_unit'][$i]) ? 0 : 1;

            //     array_push($product_units, array (
            //         'code' => $request->product_unit_code[$i],
            //         'company_id' => $request->company_id,
            //         'product_id' => Hashids::decode($product)[0],
            //         'unit_id' => Hashids::decode($request['unit_id'][$i])[0],
            //         'is_base' => $is_base,
            //         'conv_value' => $request['conv_value'][$i],
            //         'is_primary_unit' => $is_primary_unit,
            //         'remarks' => $request['remarks']
            //     ));
            // }
    
            // foreach ($product_units as $product_unit) {
            //     if ($product_unit['code'] == '[AUTO]') {
            //         $product_unit['code'] = $faker->creditCardNumber();
            //     };

            //     $result = $this->productUnitService->create(
            //         $product_unit['code'],
            //         $product_unit['company_id'],
            //         $product_unit['product_id'],
            //         $product_unit['unit_id'],
            //         $product_unit['is_base'],
            //         $product_unit['conv_value'],
            //         $product_unit['is_primary_unit'],
            //         $product_unit['remarks']
            //     );
    
            //     if ($result == 0) {
            //         return response()->error();
            //     };
            // }
        }

        # Jika Service... Maka...
        if ($request->product_type[0] == '4') {
            $company_id = $request->company_id != null ? Hashids::decode($request->company_id)[0]:$company_id = null;          
            $code = $request->code;
            $group_id = Hashids::decode($request->group_id)[0];
            $brand_id = null;
            $name = $request->name;
            $tax_status = $request->tax_status;
            $supplier_id = null;
            $remarks = $request->remarks;
            $point = $request->point;
            $is_use_serial = 0;
            $product_type = $request->product_type;
            $status = $request->status;

            $service = $this->productService->create(
                $company_id,
                $code, 
                $group_id,
                $brand_id,
                $name,
                $tax_status,
                $supplier_id,
                $remarks,
                $point,
                $is_use_serial,
                $product_type,
                $status
            );
    
            if ($service == 0) {
                return response()->error();
            };

            $product_unit = [];
            array_push($product_unit, array (
                'code' => $request->code,
                'company_id' => $request->company_id,
                'product_id' => Hashids::decode($service)[0],
                'unit_id' => Hashids::decode($request->unit_id)[0],
                'is_base' => 1,
                'conv_value' => 1,
                'is_primary_unit' => 1,
                'remarks' => null
            ));
    
            $result = $this->productUnitService->create(
                $product_unit[0]['code'],
                $product_unit[0]['company_id'],
                $product_unit[0]['product_id'],
                $product_unit[0]['unit_id'],
                $product_unit[0]['is_base'],
                $product_unit[0]['conv_value'],
                $product_unit[0]['is_primary_unit'],
                $product_unit[0]['remarks']
            );

            if ($result == 0) {
                return response()->error();
            };
        }

        return response()->success();
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => new uniqueCode('update', $id, 'products'),
            'name' => 'required|max:255',
            'status' => 'required',
        ]);

        if ($request->product_type !== '4') {
            $company_id = $request->company_id != null ? Hashids::decode($request->company_id)[0]:$company_id = null;          
            $code = $request->code;
            $group_id = Hashids::decode($request->group_id)[0];
            $brand_id = Hashids::decode($request->brand_id)[0];
            $name = $request->name;
            $tax_status = $request->tax_status;
            $supplier_id = $request->supplier_id != null ? Hashids::decode($request->supplier_id)[0]:$supplier_id = null;
            $remarks = $request->remarks;
            $point = $request->point;
            $is_use_serial = $request->is_use_serial;
            $product_type = $request->product_type;
            $status = $request->status;

            $product = $this->productService->update(
                $id,
                $company_id,
                $code, 
                $group_id,
                $brand_id,
                $name,
                $tax_status,
                $supplier_id,
                $remarks,
                $point,
                $is_use_serial,
                $product_type,
                $status
            );

            if ($product == 0) {
                return response()->error();
            };

            $product_units = [];
            $count_unit = count($request['unit_id']);
            for ($i = 0; $i < $count_unit; $i++) {
                $is_base = is_null($request['is_base'][$i]) ? 0 : 1;
                $is_primary_unit = is_null($request['is_primary_unit'][$i]) ? 0 : 1;
    
                array_push($product_units, array (
                    'code' => $request['product_unit_code'][$i],
                    'company_id' => $request->company_id,
                    'product_id' => Hashids::decode($product)[0],
                    'unit_id' => Hashids::decode($request['unit_id'][$i])[0],
                    'is_base' => $is_base,
                    'conv_value' => $request['conv_value'][$i],
                    'is_primary_unit' => $is_primary_unit,
                    'remarks' => $request['remarks']
                ));
            }
    
            foreach ($product_units as $product_unit) {
                $result = $this->productUnitService->update(
                    $id,
                    $product_unit['code'],
                    Hashids::decode($request['company_id'])[0], 
                    Hashids::decode($request['product_id'])[0],
                    Hashids::decode($request['unit_id'])[0], 
                    $product_unit['is_base'],
                    $product_unit['conv_value'],
                    $product_unit['is_primary_unit'],
                    $product_unit['remarks']
                );
    
                if ($result == 0) {
                    return response()->error();
                };
            }
        }

        if ($request->product_type == '4') {
            $company_id = $request->company_id != null ? Hashids::decode($request->company_id)[0]:$company_id = null;          
            $code = $request->code;
            $group_id = Hashids::decode($request->group_id)[0];
            $name = $request->name;
            $unit_id = Hashids::decode($request->unit_id)[0];
            $tax_status = $request->tax_status;
            $remarks = $request->remarks;
            $point = $request->point;
            $product_type = $request->product_type;
            $status = $request->status;

            $product = $this->productService->update(
                $id,
                $company_id,
                $code, 
                $group_id,
                $brand_id,
                $name,
                $tax_status,
                $supplier_id,
                $remarks,
                $point,
                $is_use_serial,
                $product_type,
                $status
            );
    
            if ($product == 0) {
                return response()->error();
            };
        }
        
        return response()->success();
    }

    public function delete($id)
    {
        $result = $this->productService->delete($id);

        return $result == 0 ? response()->error():response()->success();
    }
}
