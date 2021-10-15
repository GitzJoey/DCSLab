<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

class Unit extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;
    
    protected $fillable = [
        'code',
        'name'
    ];

    protected static $logAttributes = ['code', 'name'];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
        'symbol',
        'status',
        'remarks',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'update_at',
        'deleted_at'
    ];

    protected $appends = ['hId'];

    public function getHIdAttribute() : string
    {
        return HashIds::encode($this->attributes['id']);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
