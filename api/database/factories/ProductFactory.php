<?php

namespace Database\Factories;

use App\Enums\ProductCategoryTypeEnum;
use App\Enums\ProductTypeEnum;
use App\Enums\RecordStatusEnum;
use App\Models\Brand;
use App\Models\Company;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        // Default state: RAW_MATERIAL (Goods)
        $companyId = Company::inRandomOrder()->value('id') ?? Company::factory()->create()->id;

        $type = ProductTypeEnum::RAW_MATERIAL;
        $category = $this->getCategory(ProductCategoryTypeEnum::PRODUCT, $companyId);
        $brand = $this->getBrand($companyId);

        return [
            'company_id' => $companyId,
            'code' => strtoupper(fake()->lexify('??')).fake()->numerify('####'),
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => $this->generateName($category, $brand),
            'slug' => fake()->slug(),
            'is_taxable' => fake()->boolean(),
            'vat_rate' => fake()->numberBetween(0, 100),
            'is_price_include_vat' => fake()->boolean(),
            'is_use_serial_number' => fake()->boolean(),
            'is_expirable' => fake()->boolean(),
            'remarks' => fake()->sentence(),
            'type' => $type,
            'status' => fake()->randomElement(RecordStatusEnum::toArrayEnum()),
        ];
    }

    public function service(): Factory
    {
        return $this->state(function (array $attributes) {
            $companyId = $attributes['company_id'] ?? Company::factory()->create()->id;
            $category = $this->getCategory(ProductCategoryTypeEnum::SERVICE, $companyId);

            return [
                'type' => ProductTypeEnum::SERVICE,
                'category_id' => $category->id,
                'brand_id' => null,
                'name' => $this->generateName($category, null),
                'is_use_serial_number' => false,
                'is_expirable' => false,
            ];
        });
    }

    public function workInProgress(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => ProductTypeEnum::WORK_IN_PROGRESS,
            ];
        });
    }

    public function finishedGoods(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => ProductTypeEnum::FINISHED_GOODS,
            ];
        });
    }

    public function rawMaterial(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => ProductTypeEnum::RAW_MATERIAL,
            ];
        });
    }

    private function getCategory(ProductCategoryTypeEnum $type, int $companyId): ProductCategory
    {
        $query = ProductCategory::where('type', $type->value)->where('company_id', $companyId);

        if ($query->exists()) {
            return $query->inRandomOrder()->first();
        }

        return ProductCategory::factory()->create([
            'type' => $type,
            'company_id' => $companyId,
        ]);
    }

    private function getBrand(int $companyId): Brand
    {
        $query = Brand::where('company_id', $companyId);

        if ($query->exists()) {
            return $query->inRandomOrder()->first();
        }

        return Brand::factory()->create([
            'company_id' => $companyId,
        ]);
    }

    private function generateName(ProductCategory $category, ?Brand $brand): string
    {
        $name = $category->name;

        if ($brand) {
            $name .= ' '.$brand->name;
        }

        return $name.' '.fake()->words(3, true);
    }
}
