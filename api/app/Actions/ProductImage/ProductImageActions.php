<?php

namespace App\Actions\ProductImage;

use App\DTOs\ProductImageDTO;
use App\Helpers\ImageHelper\ImageHelper;
use App\Models\Product;
use App\Models\ProductImage;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductImageActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function upload(UploadedFile $image): ProductImage
    {
        $timer_start = microtime(true);

        try {
            $path = ImageHelper::storeAndResizeImage($image, 'products', 500, 500);

            $absolutePath = ImageHelper::getPath($path);
            $hash = $absolutePath ? md5_file($absolutePath) : null;

            if ($hash && ProductImage::where('hash', $hash)->exists()) {
                Storage::disk('public')->delete($path);

                $productImage = ProductImage::where('hash', $hash)->first();
            } else {
                $productImage = new ProductImage();
                $productImage->path = $path;
                $productImage->hash = $hash;
                $productImage->is_main = false;
                $productImage->save();
            }

            $this->flushCache();

            return $productImage;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function attachByHash(Product $product, ProductImageDTO $data): void
    {
        $timer_start = microtime(true);

        try {
            if ($data->isMain === true) {
                $product->images()->update(['is_main' => false]);
            }

            $productImage = ProductImage::where('product_id', $product->id)
                ->where('hash', $data->hash)
                ->first();

            if ($productImage) {
                $productImage->is_main = $data->isMain;
                $productImage->save();
            } else {
                $productImage = ProductImage::where('hash', $data->hash)
                    ->whereNull('product_id')
                    ->first();

                if ($productImage) {
                    $productImage->product_id = $product->id;
                    $productImage->is_main = $data->isMain;
                    $productImage->save();
                } else {
                    $productImage = ProductImage::where('hash', $data->hash)->first();

                    if ($productImage) {
                        $product->images()->create([
                            'path' => $productImage->path,
                            'hash' => $productImage->hash,
                            'is_main' => $data->isMain,
                        ]);
                    }
                }
            }

            $unusedProductImages = ProductImage::whereNull('product_id')
                ->where('created_at', '<', now()->subDay())
                ->get();

            foreach ($unusedProductImages as $productImage) {
                $isUsedElsewhere = ProductImage::where('path', $productImage->path)
                    ->whereNotNull('product_id')
                    ->exists();

                if (! $isUsedElsewhere) {
                    Storage::disk('public')->delete($productImage->path);
                }

                $productImage->delete();
            }

            $this->flushCache();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function detachById(Product $product, int $id): void
    {
        $timer_start = microtime(true);

        try {
            $productImage = ProductImage::where('product_id', $product->id)
                ->where('id', $id)
                ->first();

            if ($productImage) {
                $wasMainImage = (bool) $productImage->is_main;
                $productImage->delete();

                if ($wasMainImage) {
                    $nextMainImage = ProductImage::where('product_id', $product->id)
                        ->orderBy('id')
                        ->first();

                    if ($nextMainImage) {
                        $nextMainImage->is_main = true;
                        $nextMainImage->save();
                    }
                }
            }

            $this->flushCache();
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }
}
