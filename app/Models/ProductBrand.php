<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

class ProductBrand extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'name'
    ];

    protected static $logAttributes = ['code', 'name'];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at'
    ];

    protected $appends = ['hId'];

    public function getHIdAttribute() : string
    {
        return hashIDs::encode($this->attributes['id']);
    }
}
