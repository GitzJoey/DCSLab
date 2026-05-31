<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait HasModelEvents
{
    /**
     * Boot the HasModelEvents trait.
     * Laravel automatically executes this when the Model initializes.
     */
    public static function bootHasModelEvents(): void
    {
        static::creating(function ($model) {
            if (empty($model->ulid)) {
                $model->ulid = (string) Str::ulid();
            }

            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });

        static::deleting(function ($model) {
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                if (Auth::check()) {
                    $model->deleted_by = Auth::id();

                    $model->saveQuietly();
                }
            }
        });
    }
}
