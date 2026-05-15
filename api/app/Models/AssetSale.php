<?php

namespace App\Models;

use App\Traits\BootableModel;
use App\Traits\ScopeableByBranch;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetSale extends Model
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
        'customer_id',
        'remarks',
        'is_posted',
        'item_total',
        'rounding',
        'amount_receivable',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'due_days' => 'integer',
            'is_posted' => 'boolean',
            'item_total' => 'decimal:8',
            'rounding' => 'decimal:8',
            'amount_receivable' => 'decimal:8',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(AssetSaleItem::class);
    }
}
