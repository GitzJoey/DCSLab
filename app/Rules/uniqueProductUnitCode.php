<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

use Illuminate\Container\Container;
use App\Services\ProductService;
use Illuminate\Support\Facades\Config;

class uniqueProductUnitCode implements Rule
{
    private int $companyId;
    private int $productId;
    private ?int $exceptId;

    private ProductService $productService;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(int $companyId, int $productId, ?int $exceptId = null)
    {
        $this->companyId = $companyId;
        $this->productId = $productId;
        $this->exceptId = $exceptId ? $exceptId : null;

        $this->productService = Container::getInstance()->make(ProductService::class);
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($value == Config::get('const.DEFAULT.KEYWORDS.AUTO')) return true;

        $is_duplicate = false;

        $is_duplicate = $this->productService->isUniqueCodeForProductUnits($value, $this->companyId, $this->productId, $this->exceptId);

        return $is_duplicate;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('rules.unique_code');
    }
}
