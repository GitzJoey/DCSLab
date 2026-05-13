<?php

namespace App\Models;

use App\Enums\RecordStatusEnum;
use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'asset_category_id',
        'code',
        'name',
        'asset_unit_id',
        'status',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'status' => RecordStatusEnum::class,
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function assetCategory()
    {
        return $this->belongsTo(AssetCategory::class)->withTrashed();
    }

    public function assetUnit()
    {
        return $this->belongsTo(AssetUnit::class)->withTrashed();
    }
}
