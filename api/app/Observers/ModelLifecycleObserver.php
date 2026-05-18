<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ModelLifecycleObserver
{
    /**
     * Handle the Model "creating" event.
     */
    public function creating(Model $model): void
    {
        $table = $model->getTable();
        
        if (Schema::hasColumn($table, 'ulid') && empty($model->ulid)) {
            $model->ulid = (string) Str::ulid();
        }

        if (Auth::check()) {
            $model->created_by = Auth::id();
            $model->updated_by = Auth::id();
        }
    }

    /**
     * Handle the Model "updating" event.
     */
    public function updating(Model $model): void
    {
        if (Auth::check()) {
            $model->updated_by = Auth::id();
        }
    }

    /**
     * Handle the Model "deleting" event.
     */
    public function deleting(Model $model): void
    {
        if (Auth::check()) {
            $model->deleted_by = Auth::id();
            
            // Using saveQuietly prevents the updating() event from 
            // firing again and causing an infinite database loop.
            $model->saveQuietly();
        }
    }
}
