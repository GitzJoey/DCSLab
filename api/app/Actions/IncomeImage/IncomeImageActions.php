<?php

namespace App\Actions\IncomeImage;

use App\DTOs\IncomeImageDTO;
use App\Helpers\ImageHelper\ImageHelper;
use App\Models\Income;
use App\Models\IncomeImage;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class IncomeImageActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function upload(UploadedFile $image): IncomeImage
    {
        $timer_start = microtime(true);

        try {
            $path = ImageHelper::storeAndResizeImage($image, 'incomes', 500, 500);

            $absolutePath = ImageHelper::getPath($path);
            $hash = $absolutePath ? md5_file($absolutePath) : null;

            if ($hash && IncomeImage::where('hash', $hash)->exists()) {
                Storage::disk('public')->delete($path);

                $incomeImage = IncomeImage::where('hash', $hash)->first();
            } else {
                $incomeImage = new IncomeImage();
                $incomeImage->path = $path;
                $incomeImage->hash = $hash;
                $incomeImage->is_main = false;
                $incomeImage->save();
            }

            $this->flushCache();

            return $incomeImage;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function attachByHash(Income $income, IncomeImageDTO $data): void
    {
        $timer_start = microtime(true);

        try {
            if ($data->isMain === true) {
                $income->images()->update(['is_main' => false]);
            }

            $incomeImage = IncomeImage::where('income_id', $income->id)
                ->where('hash', $data->hash)
                ->first();

            if ($incomeImage) {
                $incomeImage->is_main = $data->isMain;
                $incomeImage->save();
            } else {
                $incomeImage = IncomeImage::where('hash', $data->hash)
                    ->whereNull('income_id')
                    ->first();

                if ($incomeImage) {
                    $incomeImage->income_id = $income->id;
                    $incomeImage->is_main = $data->isMain;
                    $incomeImage->save();
                } else {
                    $incomeImage = IncomeImage::where('hash', $data->hash)->first();

                    if ($incomeImage) {
                        $income->images()->create([
                            'path' => $incomeImage->path,
                            'hash' => $incomeImage->hash,
                            'is_main' => $data->isMain,
                        ]);
                    }
                }
            }

            $unusedIncomeImages = IncomeImage::whereNull('income_id')
                ->where('created_at', '<', now()->subDay())
                ->get();

            foreach ($unusedIncomeImages as $incomeImage) {
                $isUsedElsewhere = IncomeImage::where('path', $incomeImage->path)
                    ->whereNotNull('income_id')
                    ->exists();

                if (! $isUsedElsewhere) {
                    Storage::disk('public')->delete($incomeImage->path);
                }

                $incomeImage->delete();
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

    public function detachById(Income $income, int $id): void
    {
        $timer_start = microtime(true);

        try {
            $incomeImage = IncomeImage::where('income_id', $income->id)
                ->where('id', $id)
                ->first();

            if ($incomeImage) {
                $wasMainImage = (bool) $incomeImage->is_main;
                $incomeImage->delete();

                if ($wasMainImage) {
                    $nextMainImage = IncomeImage::where('income_id', $income->id)
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
