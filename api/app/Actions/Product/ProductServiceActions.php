<?php

namespace App\Actions\Product;

use App\Actions\ProductImage\ProductImageActions;
use App\Actions\ProductUnit\ProductUnitActions;
use App\DTOs\ProductImageDTO;
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

class ProductServiceActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct(
        private ProductUnitActions $productUnitActions,
        private ProductImageActions $productImageActions,
    ) {
    }

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
            $product->is_taxable = $data->isTaxable;
            $product->vat_rate = $data->vatRate;
            $product->is_price_include_vat = $data->isPriceIncludeVat;
            $product->is_use_serial_number = false;
            $product->is_expirable = false;
            $product->remarks = $data->remarks;
            $product->type = ProductTypeEnum::SERVICE->value;
            $product->status = $data->status;
            $product->save();

            $productUnitDTO = new ProductUnitCreateServiceDTO(
                companyId: $product->company_id,
                productId: $product->id,
                remarks: null,
                unitId: $data->unitId,
                price: $data->price,
                point: $data->point,
            );

            $this->productUnitActions->createService($productUnitDTO);

            foreach ($data->images as $image) {
                $productImageDTO = new ProductImageDTO(
                    hash: $image['hash'],
                    isMain: (bool) $image['is_main'],
                );

                $this->productImageActions->attachByHash($product, $productImageDTO);
            }

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
            $product->is_taxable = $data->isTaxable;
            $product->vat_rate = $data->vatRate;
            $product->is_price_include_vat = $data->isPriceIncludeVat;
            $product->is_use_serial_number = false;
            $product->is_expirable = false;
            $product->remarks = $data->remarks;
            $product->type = ProductTypeEnum::SERVICE->value;
            $product->status = $data->status;
            $product->save();

            $productUnit = $product->productUnits()->firstOrFail();

            $updateDto = new ProductUnitUpdateServiceDTO(
                remarks: null,
                unitId: $data->unitId,
                price: $data->price,
                point: $data->point,
            );

            $this->productUnitActions->updateService($productUnit, $updateDto);

            foreach ($data->deleteImageIds as $imageId) {
                $this->productImageActions->detachById($product, $imageId);
            }

            foreach ($data->images as $image) {
                $productImageDTO = new ProductImageDTO(
                    hash: $image['hash'],
                    isMain: (bool) $image['is_main'],
                );

                $this->productImageActions->attachByHash($product, $productImageDTO);
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
