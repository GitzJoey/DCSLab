<?php

namespace App\Http\Controllers;

use App\Services\ProductService;

use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Http\Request;
use App\Http\Requests\ProductRequest;
use Illuminate\Support\Facades\Config;

class ProductController extends BaseController
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->productService = $productService;
    }

    public function readProducts(Request $request)
    {
        $search = $request->has('search') && !is_null($request['search']) ? $request['search']:'';
        $paginate = true;
        $perPage = $request->has('perPage') ? $request['perPage']:null;

        $companyId = Hashids::decode($request['companyId'])[0];

        return $this->productService->read(
                    companyId: $companyId, 
                    isProduct: true, 
                    isService: false, 
                    search: $search, 
                    paginate: $paginate, 
                    perPage: $perPage);
    }

    public function readServices(Request $request)
    {
        $search = $request->has('search') && !is_null($request['search']) ? $request['search']:'';
        $paginate = true;
        $perPage = $request->has('perPage') ? $request['perPage']:null;

        $companyId = Hashids::decode($request['companyId'])[0];

        return $this->productService->read(
                    companyId: $companyId, 
                    isProduct: false, 
                    isService: true, 
                    search: $search, 
                    paginate: $paginate, 
                    perPage: $perPage);
    }

    public function store(ProductRequest $productRequest)
    {   
        $request = $productRequest->validated();
        
        $company_id = Hashids::decode($request['company_id'])[0];

        $code = $request['code'];
        $product_group_id = Hashids::decode($request['product_group_id'])[0];
        $brand_id = Hashids::decode($request['brand_id'])[0];
        $name = $request['name'];

        if (array_key_exists('supplier_id', $request) && $request['supplier_id'] != '') {
            $supplier_id =  Hashids::decode($request['supplier_id'])[0];
        }

        $remarks = $request['remarks'];
        $point = $request['point'];

        $use_serial_number = array_key_exists('use_serial_number', $request) ? true : false;
        $has_expiry_date = array_key_exists('has_expiry_date', $request) ? true : false;

        $product_type = $request['product_type'];
        $status = $request['status'];

        $taxable_supplies = array_key_exists('taxable_supplies', $request) ? boolVal($request['taxable_supplies']) : false;
        $rate_supplies = array_key_exists('rate_supplies', $request) ? intVal($request['rate_supplies']) : 0;
        $price_include_vat = array_key_exists('price_include_vat', $request) ? boolVal($request['price_include_vat']) : false;

        $product_units = [];
        $count_unit = count($request['unit_id']);
        for ($i = 0; $i < $count_unit; $i++) {
            $is_base = $request['is_base'][$i] == '1' ? true : false;

            $is_primary_unit = $request['is_primary_unit'][$i] == '1' ? true : false;

            array_push($product_units, array (
                'company_id' => $company_id,
                'code' => $code,
                'unit_id' => Hashids::decode($request['unit_id'][$i])[0],
                'conv_value' => $request['conv_value'][$i],
                'is_base' => $is_base,
                'is_primary_unit' => $is_primary_unit,
                'remarks' => ''
            ));
        }

        $result = $this->productService->create(
            $company_id,
            $code, 
            $product_group_id,
            $brand_id,
            $name,
            $taxable_supplies,
            $rate_supplies,
            $price_include_vat,
            $supplier_id,
            $remarks,
            $point,
            $use_serial_number,
            $has_expiry_date,
            $product_type,
            $status,
            $product_units
        );

        return is_null($result) ? response()->error():response()->success();
    }

    public function update($id, ProductRequest $productRequest)
    {
        $request = $productRequest->validated();

        $code = $request['code'];
        $company_id = Hashids::decode($request['$company_id'])[0];
        $product_group_id = Hashids::decode($request['product_group_id'])[0];
        $brand_id = Hashids::decode($request['brand_id'])[0];
        $name = $request['name'];
        $taxable_supplies = $request['taxable_supplies'];
        $rate_supplies = $request['rate_supplies'];
        $price_include_vat = $request['price_include_vat'];
        $supplier_id = array_key_exists('supplier_id', $request) != null ? Hashids::decode($request['supplier_id'])[0]:null;
        $remarks = $request['remarks'];
        $point = $request['point'];
        $use_serial_number = $request['use_serial_number'];
        $has_expiry_date = $request['has_expiry_date'];
        $product_type = $request['product_type'];
        $status = $request['status'];

        $product_units = [];
        $count_unit = count($request['unit_id']);
        for ($i = 0; $i < $count_unit; $i++) {
            $product_unit_id = $request['product_unit_hId'][$i] != null ? Hashids::decode($request['product_unit_hId'][$i])[0] : null;

            $code = '';

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
                'code' => $code,
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
            $taxable_supplies,
            $rate_supplies,
            $price_include_vat,    
            $supplier_id,
            $remarks,
            $point,
            $use_serial_number,
            $has_expiry_date,
            $product_type,
            $status,
            $product_units
        );

        return is_null($product) ? response()->error() : response()->success();
    }

    public function delete($id)
    {
        $result = $this->productService->delete($id);

        return $result ? response()->error():response()->success();
    }

    public function getProductType(Request $request)
    {
        if ($request->has('type') && $request['type'] == 'products') {
            return [
                ['name' => 'components.dropdown.values.productTypeDDL.raw', 'code' => 1],
                ['name' => 'components.dropdown.values.productTypeDDL.wip', 'code' => 2],
                ['name' => 'components.dropdown.values.productTypeDDL.fg', 'code' => 3],
            ];
        } else if ($request->has('type') && $request['type'] == 'service'){
            return [
                ['name' => 'components.dropdown.values.productTypeDDL.svc', 'code' => 4]            
            ];
        } else {
            return [
                ['name' => 'components.dropdown.values.productTypeDDL.raw', 'code' => 1],
                ['name' => 'components.dropdown.values.productTypeDDL.wip', 'code' => 2],
                ['name' => 'components.dropdown.values.productTypeDDL.fg', 'code' => 3],
                ['name' => 'components.dropdown.values.productTypeDDL.svc', 'code' => 4]            
            ];
        }
    }
}
