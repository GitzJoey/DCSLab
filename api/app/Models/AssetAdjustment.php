<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetAdjustment extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByBranch;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'code',
        'date',
        'remarks',
        'is_posted',
        'total_incoming_asset_qty',
        'total_outgoing_asset_qty',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'is_posted' => 'boolean',
            'total_incoming_asset_qty' => 'decimal:8',
            'total_outgoing_asset_qty' => 'decimal:8',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class)->withTrashed();
    }

    public function inItems()
    {
        return $this->hasMany(AssetAdjustmentInItem::class);
    }

    public function outItems()
    {
        return $this->hasMany(AssetAdjustmentOutItem::class);
    }
}
