<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Branch;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Vinkla\Hashids\Facades\Hashids;

class Company extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'status'
    ];

    protected static $logAttributes = ['code', 'name', 'status'];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'id',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $appends = ['hId'];

    public function getHIdAttribute() : string
    {
        return HashIds::encode($this->attributes['id']);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    } 

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    } 
}
