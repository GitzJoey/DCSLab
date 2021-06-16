<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Branch;
use App\Models\Warehouse;
use Spatie\Activitylog\Traits\LogsActivity;

class Company extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'name',
        'is_active'
    ];

    protected static $logAttributes = ['code', 'name', 'is_active'];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    } 

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class);
    } 
}
