<?php

namespace App\Models;

use App\Models\Company;
use App\Models\ProductUnit;
use App\Traits\ScopeableByCompany;
use Spatie\Activitylog\LogOptions;
use App\Enums\ProductCategory;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Unit extends Model
{
    use LogsActivity;
    use SoftDeletes;

    use ScopeableByCompany;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'description',
        'category'
    ];

    protected static $logAttributes = [
        'code',
        'name',
        'description',
        'category'
    ];

    protected static $logOnlyDirty = true;

    protected $hidden = [];

    protected $casts = [
        'category' => ProductCategory::class
    ];

    public function hId() : Attribute
    {
        return Attribute::make(
            get: fn () => HashIds::encode($this->attributes['id'])
        );
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}