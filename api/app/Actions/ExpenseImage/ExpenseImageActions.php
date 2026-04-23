<?php

namespace App\Actions\ExpenseImage;

use App\DTOs\ExpenseImageDTO;
use App\Helpers\ImageHelper\ImageHelper;
use App\Models\Expense;
use App\Models\ExpenseImage;
use App\Traits\CacheHelper;
use App\Traits\LoggerHelper;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ExpenseImageActions
{
    use CacheHelper;
    use LoggerHelper;

    public function __construct()
    {
    }

    public function upload(UploadedFile $image): ExpenseImage
    {
        $timer_start = microtime(true);

        try {
            $path = ImageHelper::storeAndResizeImage($image, 'expenses', 500, 500);

            $absolutePath = ImageHelper::getPath($path);
            $hash = $absolutePath ? md5_file($absolutePath) : null;

            if ($hash && ExpenseImage::where('hash', $hash)->exists()) {
                Storage::disk('public')->delete($path);

                $expenseImage = ExpenseImage::where('hash', $hash)->first();
            } else {
                $expenseImage = new ExpenseImage();
                $expenseImage->path = $path;
                $expenseImage->hash = $hash;
                $expenseImage->is_main = false;
                $expenseImage->save();
            }

            $this->flushCache();

            return $expenseImage;
        } catch (Exception $e) {
            $this->loggerDebug(__METHOD__, $e);
            throw $e;
        } finally {
            $execution_time = microtime(true) - $timer_start;
            $this->loggerPerformance(__METHOD__, $execution_time);
        }
    }

    public function attachByHash(Expense $expense, ExpenseImageDTO $data): void
    {
        $timer_start = microtime(true);

        try {
            if ($data->isMain === true) {
                $expense->images()->update(['is_main' => false]);
            }

            $expenseImage = ExpenseImage::where('expense_id', $expense->id)
                ->where('hash', $data->hash)
                ->first();

            if ($expenseImage) {
                $expenseImage->is_main = $data->isMain;
                $expenseImage->save();
            } else {
                $expenseImage = ExpenseImage::where('hash', $data->hash)
                    ->whereNull('expense_id')
                    ->first();

                if ($expenseImage) {
                    $expenseImage->expense_id = $expense->id;
                    $expenseImage->is_main = $data->isMain;
                    $expenseImage->save();
                } else {
                    $expenseImage = ExpenseImage::where('hash', $data->hash)->first();

                    if ($expenseImage) {
                        $expense->images()->create([
                            'path' => $expenseImage->path,
                            'hash' => $expenseImage->hash,
                            'is_main' => $data->isMain,
                        ]);
                    }
                }
            }

            $unusedExpenseImages = ExpenseImage::whereNull('expense_id')
                ->where('created_at', '<', now()->subDay())
                ->get();

            foreach ($unusedExpenseImages as $expenseImage) {
                $isUsedElsewhere = ExpenseImage::where('path', $expenseImage->path)
                    ->whereNotNull('expense_id')
                    ->exists();

                if (! $isUsedElsewhere) {
                    Storage::disk('public')->delete($expenseImage->path);
                }

                $expenseImage->delete();
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

    public function detachById(Expense $expense, int $id): void
    {
        $timer_start = microtime(true);

        try {
            $expenseImage = ExpenseImage::where('expense_id', $expense->id)
                ->where('id', $id)
                ->first();

            if ($expenseImage) {
                $wasMainImage = (bool) $expenseImage->is_main;
                $expenseImage->delete();

                if ($wasMainImage) {
                    $nextMainImage = ExpenseImage::where('expense_id', $expense->id)
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
