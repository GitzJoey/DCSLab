<?php

namespace App\Actions\PrepaidExpenseImage;

use App\DTOs\PrepaidExpenseImageDTO;
use App\Helpers\ImageHelper\ImageHelper;
use App\Models\PrepaidExpense;
use App\Models\PrepaidExpenseImage;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PrepaidExpenseImageActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function upload(UploadedFile $image): PrepaidExpenseImage
    {
        $timer_start = microtime(true);

        try {
            $path = ImageHelper::storeAndResizeImage($image, 'prepaid_expenses', 500, 500);

            $absolutePath = ImageHelper::getPath($path);
            $hash = $absolutePath ? md5_file($absolutePath) : null;

            if ($hash && PrepaidExpenseImage::where('hash', $hash)->exists()) {
                Storage::disk('public')->delete($path);

                $prepaidExpenseImage = PrepaidExpenseImage::where('hash', $hash)->first();
            } else {
                $prepaidExpenseImage = new PrepaidExpenseImage();
                $prepaidExpenseImage->path = $path;
                $prepaidExpenseImage->hash = $hash;
                $prepaidExpenseImage->is_main = false;
                $prepaidExpenseImage->save();
            }

            $this->flushCache();

            return $prepaidExpenseImage;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function attachByHash(PrepaidExpense $prepaidExpense, PrepaidExpenseImageDTO $data): void
    {
        $timer_start = microtime(true);

        try {
            if ($data->isMain === true) {
                $prepaidExpense->images()->update(['is_main' => false]);
            }

            $prepaidExpenseImage = PrepaidExpenseImage::where('prepaid_expense_id', $prepaidExpense->id)
                ->where('hash', $data->hash)
                ->first();

            if ($prepaidExpenseImage) {
                $prepaidExpenseImage->is_main = $data->isMain;
                $prepaidExpenseImage->save();
            } else {
                $prepaidExpenseImage = PrepaidExpenseImage::where('hash', $data->hash)
                    ->whereNull('prepaid_expense_id')
                    ->first();

                if ($prepaidExpenseImage) {
                    $prepaidExpenseImage->prepaid_expense_id = $prepaidExpense->id;
                    $prepaidExpenseImage->is_main = $data->isMain;
                    $prepaidExpenseImage->save();
                } else {
                    $prepaidExpenseImage = PrepaidExpenseImage::where('hash', $data->hash)->first();

                    if ($prepaidExpenseImage) {
                        $prepaidExpense->images()->create([
                            'path' => $prepaidExpenseImage->path,
                            'hash' => $prepaidExpenseImage->hash,
                            'is_main' => $data->isMain,
                        ]);
                    }
                }
            }

            $unusedPrepaidExpenseImages = PrepaidExpenseImage::whereNull('prepaid_expense_id')
                ->where('created_at', '<', now()->subDay())
                ->get();

            foreach ($unusedPrepaidExpenseImages as $prepaidExpenseImage) {
                $isUsedElsewhere = PrepaidExpenseImage::where('path', $prepaidExpenseImage->path)
                    ->whereNotNull('prepaid_expense_id')
                    ->exists();

                if (! $isUsedElsewhere) {
                    Storage::disk('public')->delete($prepaidExpenseImage->path);
                }

                $prepaidExpenseImage->delete();
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

    public function detachById(PrepaidExpense $prepaidExpense, int $id): void
    {
        $timer_start = microtime(true);

        try {
            $prepaidExpenseImage = PrepaidExpenseImage::where('prepaid_expense_id', $prepaidExpense->id)
                ->where('id', $id)
                ->first();

            if ($prepaidExpenseImage) {
                $wasMainImage = (bool) $prepaidExpenseImage->is_main;
                $prepaidExpenseImage->delete();

                if ($wasMainImage) {
                    $nextMainImage = PrepaidExpenseImage::where('prepaid_expense_id', $prepaidExpense->id)
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
