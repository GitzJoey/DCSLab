<?php

namespace App\Actions\PrepaidIncomeImage;

use App\DTOs\PrepaidIncomeImageDTO;
use App\Helpers\ImageHelper\ImageHelper;
use App\Models\PrepaidIncome;
use App\Models\PrepaidIncomeImage;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PrepaidIncomeImageActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function upload(UploadedFile $image): PrepaidIncomeImage
    {
        $timer_start = microtime(true);

        try {
            $path = ImageHelper::storeAndResizeImage($image, 'prepaid_incomes', 500, 500);

            $absolutePath = ImageHelper::getPath($path);
            $hash = $absolutePath ? md5_file($absolutePath) : null;

            if ($hash && PrepaidIncomeImage::where('hash', $hash)->exists()) {
                Storage::disk('public')->delete($path);

                $prepaidIncomeImage = PrepaidIncomeImage::where('hash', $hash)->first();
            } else {
                $prepaidIncomeImage = new PrepaidIncomeImage();
                $prepaidIncomeImage->path = $path;
                $prepaidIncomeImage->hash = $hash;
                $prepaidIncomeImage->is_main = false;
                $prepaidIncomeImage->save();
            }

            $this->flushCache();

            return $prepaidIncomeImage;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function attachByHash(PrepaidIncome $prepaidIncome, PrepaidIncomeImageDTO $data): void
    {
        $timer_start = microtime(true);

        try {
            if ($data->isMain === true) {
                $prepaidIncome->images()->update(['is_main' => false]);
            }

            $prepaidIncomeImage = PrepaidIncomeImage::where('prepaid_income_id', $prepaidIncome->id)
                ->where('hash', $data->hash)
                ->first();

            if ($prepaidIncomeImage) {
                $prepaidIncomeImage->is_main = $data->isMain;
                $prepaidIncomeImage->save();
            } else {
                $prepaidIncomeImage = PrepaidIncomeImage::where('hash', $data->hash)
                    ->whereNull('prepaid_income_id')
                    ->first();

                if ($prepaidIncomeImage) {
                    $prepaidIncomeImage->prepaid_income_id = $prepaidIncome->id;
                    $prepaidIncomeImage->is_main = $data->isMain;
                    $prepaidIncomeImage->save();
                } else {
                    $prepaidIncomeImage = PrepaidIncomeImage::where('hash', $data->hash)->first();

                    if ($prepaidIncomeImage) {
                        $prepaidIncome->images()->create([
                            'path' => $prepaidIncomeImage->path,
                            'hash' => $prepaidIncomeImage->hash,
                            'is_main' => $data->isMain,
                        ]);
                    }
                }
            }

            $unusedPrepaidIncomeImages = PrepaidIncomeImage::whereNull('prepaid_income_id')
                ->where('created_at', '<', now()->subDay())
                ->get();

            foreach ($unusedPrepaidIncomeImages as $prepaidIncomeImage) {
                $isUsedElsewhere = PrepaidIncomeImage::where('path', $prepaidIncomeImage->path)
                    ->whereNotNull('prepaid_income_id')
                    ->exists();

                if (! $isUsedElsewhere) {
                    Storage::disk('public')->delete($prepaidIncomeImage->path);
                }

                $prepaidIncomeImage->delete();
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

    public function detachById(PrepaidIncome $prepaidIncome, int $id): void
    {
        $timer_start = microtime(true);

        try {
            $prepaidIncomeImage = PrepaidIncomeImage::where('prepaid_income_id', $prepaidIncome->id)
                ->where('id', $id)
                ->first();

            if ($prepaidIncomeImage) {
                $wasMainImage = (bool) $prepaidIncomeImage->is_main;
                $prepaidIncomeImage->delete();

                if ($wasMainImage) {
                    $nextMainImage = PrepaidIncomeImage::where('prepaid_income_id', $prepaidIncome->id)
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
