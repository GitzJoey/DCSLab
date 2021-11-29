<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Services\ProductUnitService;

use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Config;
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
        if (!parent::hasSelectedCompanyOrCompany())
        return response()->error(trans('error_messages.unable_to_find_selected_company'));

        $userId = Auth::user()->id;
        return $this->productService->read_product($userId);
    }

    public function read_service()
    {
        $userId = Auth::user()->id;
        return $this->productService->read_service($userId);
    }

    public function store(Request $request)
    {   
        // apabila product maka...
        if ($request->product_type[0] !== '4') {
            $request->validate([
                'code' => ['required', 'max:255', new uniqueCode('create', '', 'products')],
                'name' => 'required|max:255',
                'group_id' => 'required',
                'brand_id' => 'required',
                'unit_id' => 'required',
                'supplier_id' => 'required',
                'status' => 'required',
            ]);
        }

        // apabila service maka...
        if ($request->product_type[0] == '4') {
        $request->validate([
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'products')],
            'name' => 'required|max:255',
            'group_id' => 'required',
            'unit_id' => 'required',
            'status' => 'required',
        ]);
        }
        
        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        if ($request['code'] == 'AUTO') {
            $randomGenerator = new randomGenerator();
            $request['code'] = $randomGenerator->generateNumber(10000, 99999);
        };

        // apabila product maka...
        if ($request->product_type[0] !== '4') {
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
                if ($request->product_unit_code[$i] == 'AUTO') {
                    $randomGenerator = new randomGenerator();
                    $new_product_unit_code = $randomGenerator->generateNumber(10000, 99999);
                } else {
                    $new_product_unit_code = $request->product_unit_code[$i];
                };

                $is_base = is_null($request['is_base'][$i]) ? 0 : $request['is_base'][$i];
                $is_base = is_numeric($is_base) ? $is_base : 0;

                $is_primary_unit = is_null($request['is_primary_unit'][$i]) ? 0 : is_null($request['is_primary_unit'][$i]);
                $is_primary_unit = is_numeric($is_primary_unit) ? $is_primary_unit : 0;

                array_push($product_units, array (
                    'code' => $new_product_unit_code,
                    'company_id' => $company_id,
                    'product_id' => null,
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
        }

        // apabila service maka...
        if ($request->product_type[0] == '4') {
            $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
            $company_id = Hashids::decode($company_id)[0];

            $code = $request['code'];
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

            $product_units = [];
            array_push($product_units, array (
                'code' => $request['code'],
                'company_id' => $company_id,
                'product_id' => null,
                'unit_id' => Hashids::decode($request->unit_id)[0],
                'is_base' => 1,
                'conv_value' => 1,
                'is_primary_unit' => 1,
                'remarks' => $request['remarks']
            ));

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
                $status,
                $product_units
            );
    
            if ($service == 0) {
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
            $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
            $company_id = Hashids::decode($company_id)[0];

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
                $product_unit_id = $request['product_unit_hId'][$i] != null ? Hashids::decode($request['product_unit_hId'][$i])[0] : null;

                if ($request->product_unit_code[$i] == 'AUTO') {
                    $randomGenerator = new randomGenerator();
                    $new_product_unit_code = $randomGenerator->generateNumber(10000, 99999);
                } else {
                    $new_product_unit_code = $request->product_unit_code[$i];
                };

                $is_base = is_null($request['is_base'][$i]) ? 0 : $request['is_base'][$i];
                $is_base = is_numeric($is_base) ? $is_base : 0;

                $is_primary_unit = is_null($request['is_primary_unit'][$i]) ? 0 : $request['is_primary_unit'][$i];
                $is_primary_unit = is_numeric($is_primary_unit) ? $is_primary_unit : 0;

                array_push($product_units, array (
                    'id' => $product_unit_id,                    
                    'code' => $new_product_unit_code,
                    'company_id' => $company_id,
                    'product_id' => $id,
                    'unit_id' => Hashids::decode($request['unit_id'][$i])[0],
                    'conv_value' => $request['conv_value'][$i],
                    'is_base' => $is_base,
                    'is_primary_unit' => $is_primary_unit,
                    'remarks' => $request['remarks']
                ));
            }

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
                $status,
                $product_units
            );
            if ($product == 0) {
                return response()->error();
            };
        }

        if ($request->product_type == '4') {
            $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
            $company_id = Hashids::decode($company_id)[0];

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
                $status,
                $product_units
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
