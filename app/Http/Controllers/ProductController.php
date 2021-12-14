<?php

namespace App\Http\Controllers;

use App\Rules\uniqueCode;
use Illuminate\Http\Request;
use App\Services\ProductService;
use App\Services\ProductUnitService;

use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Config;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;

use App\Actions\RandomGenerator;
use App\Models\Product;

class ProductController extends BaseController
{
    private $productService;
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
        if (!parent::hasSelectedCompanyOrCompany())
        return response()->error(trans('error_messages.unable_to_find_selected_company'));
        
        $userId = Auth::user()->id;
        return $this->productService->read_service($userId);
    }

    public function store(Request $request)
    {   
        // apabila product maka...
        if ($request->product_type[0] !== '4') {
            $request->validate([
                'code' => ['required', 'max:255', new uniqueCode('create', '', 'products')],
                'name' => 'required|min:3|max:255',
                'product_group_id' => 'required',
                'brand_id' => 'required',
                'unit_id' => 'required',
                'status' => 'required',
            ]);
        }

        // apabila service maka...
        if ($request->product_type[0] == '4') {
        $request->validate([
            'code' => ['required', 'max:255', new uniqueCode('create', '', 'products')],
            'name' => 'required|min:3|max:255',
            'product_group_id' => 'required',
            'unit_id' => 'required',
            'status' => 'required',
        ]);
        }
        
        $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        $company_id = Hashids::decode($company_id)[0];

        $randomGenerator = new randomGenerator();
        $code = $request['code'];
        if ($code == 'AUTO') {
            $code_count = 1;
            do {
                $code = $randomGenerator->generateOne(99999999);
                $code_count = Product::where('code', $code)->count();
            }
            while ($code_count != 0);
        };

        // apabila product maka...
        if ($request->product_type[0] !== '4') {
            $code = $request->code;
            $product_group_id = Hashids::decode($request->product_group_id)[0];
            $brand_id = Hashids::decode($request->brand_id)[0];
            $name = $request->name;
            $tax_status = $request->tax_status;
            
            $supplier_id = $request->supplier_id != null ? $request->supplier_id : null;
            $supplier_id = $request->supplier_id != '' ? Hashids::decode($request->supplier_id)[0] : $supplier_id = null;
            
            $remarks = $request->remarks;
            $point = $request->point;

            $use_serial_number = is_null($request['use_serial_number']) ? 0 : $request['use_serial_number'];
            $use_serial_number = is_numeric($use_serial_number) ? $use_serial_number : 0;
            $use_serial_number == 'on' ? $use_serial_number = 1 : $use_serial_number = 0;
    
            $has_expiry_date = is_null($request['has_expiry_date']) ? 0 : $request['has_expiry_date'];
            $has_expiry_date = is_numeric($has_expiry_date) ? $has_expiry_date : 0;
            $has_expiry_date == 'on' ? $has_expiry_date = 1 : $has_expiry_date = 0;
            
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
                    'company_id' => $company_id,
                    'product_id' => null,
                    'code' => $new_product_unit_code,
                    'unit_id' => Hashids::decode($request['unit_id'][$i])[0],
                    'conv_value' => $request['conv_value'][$i],
                    'is_base' => $is_base,
                    'is_primary_unit' => $is_primary_unit,
                    'remarks' => ''
                ));
            }

            $product = $this->productService->create(
                $company_id,
                $code, 
                $product_group_id,
                $brand_id,
                $name,
                $tax_status,
                $supplier_id,
                $remarks,
                $point,
                $use_serial_number,
                $has_expiry_date,
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
            $code = $request['code'];
            $product_group_id = Hashids::decode($request->product_group_id)[0];
            $brand_id = null;
            $name = $request->name;
            $tax_status = $request->tax_status;
            $supplier_id = null;
            $remarks = $request->remarks;
            $point = $request->point;
            $use_serial_number = null;
            $has_expiry_date = null;
            $product_type = $request->product_type;
            $status = $request->status;

            $product_units = [];
            array_push($product_units, array (
                'company_id' => $company_id,
                'product_id' => null,
                'code' => $request['code'],
                'unit_id' => Hashids::decode($request->unit_id)[0],
                'conv_value' => 1,
                'is_base' => 1,
                'is_primary_unit' => 1,
                'remarks' => ''
            ));

            $service = $this->productService->create(
                $company_id,
                $code, 
                $product_group_id,
                $brand_id,
                $name,
                $tax_status,
                $supplier_id,
                $remarks,
                $point,
                $use_serial_number,
                $has_expiry_date,
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
            'name' => 'required|min:3|max:255',
            'status' => 'required',
        ]);

        if ($request->product_type !== '4') {
            $company_id = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
            $company_id = Hashids::decode($company_id)[0];

            $code = $request->code;
            $product_group_id = Hashids::decode($request->product_group_id)[0];
            $brand_id = Hashids::decode($request->brand_id)[0];
            $name = $request->name;
            $tax_status = $request->tax_status;
            $supplier_id = $request->supplier_id != null ? Hashids::decode($request->supplier_id)[0]:$supplier_id = null;
            $remarks = $request->remarks;
            $point = $request->point;
            $use_serial_number = $request->use_serial_number;
            $has_expiry_date = $request->has_expiry_date;
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

                $use_serial_number = $use_serial_number == 'on' ? 1 : $use_serial_number;
                $use_serial_number = is_null($use_serial_number) ? 0 : $use_serial_number;
                $use_serial_number = is_numeric($use_serial_number) ? $use_serial_number : 0;
                
                $has_expiry_date = $has_expiry_date == 'on' ? 1 : $has_expiry_date;
                $has_expiry_date = is_null($has_expiry_date) ? 0 : $has_expiry_date;
                $has_expiry_date = is_numeric($has_expiry_date) ? $has_expiry_date : 0;

                array_push($product_units, array (
                    'id' => $product_unit_id,
                    'company_id' => $company_id,
                    'product_id' => $id,             
                    'code' => $new_product_unit_code,
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
                $product_group_id,
                $brand_id,
                $name,
                $tax_status,
                $supplier_id,
                $remarks,
                $point,
                $use_serial_number,
                $has_expiry_date,
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
            $product_group_id = Hashids::decode($request->product_group_id)[0];
            $brand_id = null;
            $name = $request->name;
            $tax_status = $request->tax_status;
            $supplier_id = null;
            $remarks = $request->remarks;
            $point = $request->point;
            $use_serial_number = null;
            $has_expiry_date = null;
            $product_type = 4;
            $status = $request->status;

            $use_serial_number = $use_serial_number == 'on' ? 1 : $use_serial_number;
            $use_serial_number = is_null($use_serial_number) ? 0 : $use_serial_number;
            $use_serial_number = is_numeric($use_serial_number) ? $use_serial_number : 0;
            
            $has_expiry_date = $has_expiry_date == 'on' ? 1 : $has_expiry_date;
            $has_expiry_date = is_null($has_expiry_date) ? 0 : $has_expiry_date;
            $has_expiry_date = is_numeric($has_expiry_date) ? $has_expiry_date : 0;

            $product_units = [];
            $product_unit_id = $request['product_unit_hId'] != null ? Hashids::decode($request['product_unit_hId'])[0] : null;
            array_push($product_units, array (
                'id' => $product_unit_id,                    
                'code' => $request->code,
                'company_id' => $company_id,
                'product_id' => $id,
                'unit_id' => Hashids::decode($request->unit_id)[0],
                'conv_value' => 1,
                'is_base' => 1,
                'is_primary_unit' => 1,
                'remarks' => ''
            ));

            $product = $this->productService->update(
                $id,
                $company_id,
                $code, 
                $product_group_id,
                $brand_id,
                $name,
                $tax_status,
                $supplier_id,
                $remarks,
                $point,
                $use_serial_number,
                $has_expiry_date,
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
