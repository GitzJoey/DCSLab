<?php

namespace App\Actions\Product;

use App\Actions\ProductImage\ProductImageActions;
use App\Actions\ProductUnit\ProductUnitActions;
use App\DTOs\ProductImageDTO;
use App\DTOs\ProductPhysicalCreateDTO;
use App\DTOs\ProductPhysicalUpdateDTO;
use App\DTOs\ProductUnitCreatePhysicalDTO;
use App\DTOs\ProductUnitUpdatePhysicalDTO;
use App\Enums\ProductTypeEnum;
use App\Models\Company;
use App\Models\Product;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;

class ProductPhysicalActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct(
        private ProductUnitActions $productUnitActions,
        private ProductImageActions $productImageActions,
    ) {
    }

    public function create(ProductPhysicalCreateDTO $data): Product
    {
        $timer_start = microtime(true);

        try {
            $product = new Product();
            $product->company_id = $data->companyId;
            $product->code = $this->generateUniqueCode($data->companyId, $data->code, null);
            $product->category_id = $data->categoryId;
            $product->brand_id = $data->brandId;
            $product->default_vat_profile_id = $data->defaultVatProfileId;
            $product->name = $data->name;
            $product->is_price_include_vat = $data->isPriceIncludeVat;
            $product->is_use_serial_number = $data->isUseSerialNumber;
            $product->is_expirable = $data->isExpirable;
            $product->remarks = $data->remarks;
            $product->type = $data->type;
            $product->status = $data->status;
            $product->save();

            foreach ($data->productUnits as $productUnit) {
                $productUnitDTO = new ProductUnitCreatePhysicalDTO(
                    companyId: $product->company_id,
                    productId: $product->id,
                    code: $productUnit['code'],
                    isManufacturerSKU: $productUnit['is_manufacturer_sku'],
                    unitId: $productUnit['unit_id'],
                    price: $productUnit['price'],
                    conversionValue: $productUnit['conversion_value'],
                    isPrimaryUnit: $productUnit['is_primary_unit'],
                    point: $productUnit['point'],
                    remarks: $productUnit['remarks'],
                );

                $this->productUnitActions->createPhysical($productUnitDTO);
            }

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

    public function update(Product $product, ProductPhysicalUpdateDTO $data): Product
    {
        $timer_start = microtime(true);

        try {
            $product->code = $this->generateUniqueCode($product->company_id, $data->code, $product->id);
            $product->category_id = $data->categoryId;
            $product->brand_id = $data->brandId;
            $product->default_vat_profile_id = $data->defaultVatProfileId;
            $product->name = $data->name;
            $product->is_price_include_vat = $data->isPriceIncludeVat;
            $product->is_use_serial_number = $data->isUseSerialNumber;
            $product->is_expirable = $data->isExpirable;
            $product->remarks = $data->remarks;
            $product->type = $data->type;
            $product->status = $data->status;
            $product->save();

            foreach ($data->deleteProductUnitIds as $deleteProductUnitId) {
                $productUnit = $product->productUnits()->find($deleteProductUnitId);
                if ($productUnit) {
                    $this->productUnitActions->delete($productUnit);
                }
            }

            foreach ($data->productUnits as $productUnitData) {
                if (! isset($productUnitData['id'])) {
                    $dto = new ProductUnitCreatePhysicalDTO(
                        companyId: $product->company_id,
                        productId: $product->id,
                        code: $productUnitData['code'],
                        isManufacturerSKU: $productUnitData['is_manufacturer_sku'],
                        unitId: $productUnitData['unit_id'],
                        price: $productUnitData['price'],
                        conversionValue: $productUnitData['conversion_value'],
                        isPrimaryUnit: $productUnitData['is_primary_unit'],
                        point: $productUnitData['point'],
                        remarks: $productUnitData['remarks'],
                    );

                    $this->productUnitActions->createPhysical($dto);
                } else {
                    $productUnit = $product->productUnits()->find($productUnitData['id']);
                    if ($productUnit) {
                        $updateDto = new ProductUnitUpdatePhysicalDTO(
                            code: $productUnitData['code'],
                            isManufacturerSku: $productUnitData['is_manufacturer_sku'],
                            unitId: $productUnitData['unit_id'],
                            price: $productUnitData['price'],
                            conversionValue: $productUnitData['conversion_value'],
                            isPrimaryUnit: $productUnitData['is_primary_unit'],
                            point: $productUnitData['point'],
                            remarks: $productUnitData['remarks'],
                        );

                        $this->productUnitActions->updatePhysical($productUnit, $updateDto);
                    }
                }
            }

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
                ->where('type', '<>', ProductTypeEnum::SERVICE->value)
                ->withTrashed()
                ->count() + 1 + $tryCount;
            $code = 'PRD'.str_pad($count, 3, '0', STR_PAD_LEFT);
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
