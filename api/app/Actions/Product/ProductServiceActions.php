<?php

namespace App\Actions\Product;

use App\Actions\ProductUnit\ProductUnitActions;
use App\DTOs\ProductServiceCreateDTO;
use App\DTOs\ProductServiceUpdateDTO;
use App\DTOs\ProductUnitCreateServiceDTO;
use App\DTOs\ProductUnitUpdateServiceDTO;
use App\Enums\ProductTypeEnum;
use App\Models\Company;
use App\Models\Product;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Support\Str;

class ProductServiceActions
{
    use CacheHelper;
    use LoggerHelper;

    public function create(ProductServiceCreateDTO $data): Product
    {
        $timer_start = microtime(true);

        try {
            $product = new Product();
            $product->company_id = $data->companyId;
            $product->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $product->category_id = $data->categoryId;
            $product->brand_id = null;
            $product->name = $data->name;
            $product->slug = $this->generateUniqueSlug($data->companyId, $data->slug, $data->name, $product->code, null);
            $product->is_taxable = $data->isTaxable;
            $product->vat_rate = $data->vatRate;
            $product->is_price_include_vat = $data->isPriceIncludeVat;
            $product->is_use_serial_number = false;
            $product->is_expirable = false;
            $product->remarks = $data->remarks;
            $product->type = ProductTypeEnum::SERVICE->value;
            $product->status = $data->status;
            $product->save();

            $productUnitActions = new ProductUnitActions();
            $productUnitDTO = new ProductUnitCreateServiceDTO(
                companyId: $product->company_id,
                productId: $product->id,
                remarks: null,
                unitId: $data->unitId,
                price: $data->price,
                point: $data->point,
            );

            $productUnitActions->createService($productUnitDTO);

            $this->flushCache();

            return $product;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function update(Product $product, ProductServiceUpdateDTO $data): Product
    {
        $timer_start = microtime(true);

        try {
            $product->code = $this->generateUniqueCode($product->company_id, $data->code, $product->id);
            $product->category_id = $data->categoryId;
            $product->brand_id = null;
            $product->name = $data->name;
            $product->slug = $this->generateUniqueSlug($product->company_id, $data->slug, $data->name, $product->code, $product->id);
            $product->is_taxable = $data->isTaxable;
            $product->vat_rate = $data->vatRate;
            $product->is_price_include_vat = $data->isPriceIncludeVat;
            $product->is_use_serial_number = false;
            $product->is_expirable = false;
            $product->remarks = $data->remarks;
            $product->type = ProductTypeEnum::SERVICE->value;
            $product->status = $data->status;
            $product->save();

            $productUnitActions = new ProductUnitActions();

            $productUnit = $product->productUnits()->first();

            if ($productUnit) {
                $updateDto = new ProductUnitUpdateServiceDTO(
                    remarks: null,
                    unitId: $data->unitId,
                    price: $data->price,
                    point: $data->point,
                );

                $productUnitActions->updateService($productUnit, $updateDto);
            } else {
                $createDto = new ProductUnitCreateServiceDTO(
                    companyId: $product->company_id,
                    productId: $product->id,
                    remarks: null,
                    unitId: $data->unitId,
                    price: $data->price,
                    point: $data->point,
                );

                $productUnitActions->createService($createDto);
            }

            $this->flushCache();

            return $product->refresh();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string
    {
        if ($code != config('dcslab.KEYWORDS.AUTO')) return $code;

        $company = Company::find($companyId);

        $tryCount = 0;
        do {
            $count = $company->products()
                ->where('type', '=', ProductTypeEnum::SERVICE->value)
                ->withTrashed()
                ->count() + 1 + $tryCount;
            $code = 'SVC'.str_pad($count, 3, '0', STR_PAD_LEFT);
            $tryCount++;
        } while (! $this->isUniqueCode($companyId, $code, $exceptId));

        return $code;
    }

    public function generateUniqueSlug(int $companyId, string $baseSlug, string $productName, string $productCode, ?int $exceptId): string
    {
        $company = Company::find($companyId);
        $slug = $baseSlug;

        if ($slug === config('dcslab.KEYWORDS.AUTO'))  $slug = Str::slug($productName.' '.$productCode);

        if ($this->isUniqueSlug($companyId, $slug, $exceptId))  return $slug;

        $tryCount = 0;
        $originalSlug = $slug;
        do {
            $count = $company->products()->where('type', ProductTypeEnum::SERVICE->value)->withTrashed()->count() + 1 + $tryCount;
            $slug = $originalSlug.'-'.$count;
            $tryCount++;
        } while (! $this->isUniqueSlug($companyId, $slug, $exceptId));

        return $slug;
    }

    public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->products()->count() == 0) {
            return true;
        }

        $query = $company->products()->where('code', '=', $code);
        if ($exceptId) {
            $query->where('products.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueSlug(int $companyId, string $slug, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->products()->count() == 0) {
            return true;
        }

        $query = $company->products()->where('slug', '=', $slug);
        if ($exceptId) {
            $query->where('products.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }

    public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool
    {
        $company = Company::find($companyId);

        if ($company->products()->count() == 0) {
            return true;
        }

        $query = $company->products()->where('name', '=', $name);
        if ($exceptId) {
            $query->where('products.id', '<>', $exceptId);
        }

        return $query->doesntExist();
    }
}
