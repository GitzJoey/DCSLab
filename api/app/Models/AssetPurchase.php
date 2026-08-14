<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetPurchase extends Model
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
        'due_days',
        'supplier_id',
        'remarks',
        'is_posted',
        'item_total',
        'additional_cost',
        'rounding',
        'amount_payable',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'due_days' => 'integer',
            'is_posted' => 'boolean',
            'item_total' => 'decimal:8',
            'additional_cost' => 'decimal:8',
            'rounding' => 'decimal:8',
            'amount_payable' => 'decimal:8',
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(AssetPurchaseItem::class);
    }
}
