<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Company;
use Spatie\Activitylog\Traits\LogsActivity;

class Warehouse extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'address',
        'city',
        'contact',
        'remarks',
        'is_active'
    ];

    protected static $logAttributes = ['code', 'name', 'address', 'city','contact','remarks','is_active'];

    protected static $logOnlyDirty = true;


    protected $hidden = [
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
