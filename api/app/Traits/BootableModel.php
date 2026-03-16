<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait BootableModel
{
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->ulid = Str::ulid()->generate();

            $user = auth()->check();
            if ($user) {
                $model->created_by = auth()->id();
                $model->updated_by = auth()->id();
            }
        });

        static::updating(function ($model) {
            $user = auth()->check();
            if ($user) {
                $model->updated_by = auth()->id();
            }
        });

        static::deleting(function ($model) {
            $user = auth()->check();
            if ($user) {
                $model->deleted_by = auth()->id();
                $model->save();
            }
        });
    }
}
