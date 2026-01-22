<?php

namespace App\Models;

use App\Enums\RecordStatusEnum;
use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use BootableModel;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
        'code',
        'name',
        'address',
        'city',
        'payment_term_type',
        'payment_term',
        'taxable_enterprise',
        'tax_id',
        'status',
        'remarks',
    ];

    protected $casts = [
        'taxable_enterprise' => 'boolean',
        'status' => RecordStatusEnum::class,
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('suppliers.code', 'like', '%'.$search.'%')
                ->orWhere('suppliers.name', 'like', '%'.$search.'%')
                ->orWhere('suppliers.address', 'like', '%'.$search.'%')
                ->orWhere('suppliers.city', 'like', '%'.$search.'%')
                ->orWhere('suppliers.payment_term_type', 'like', '%'.$search.'%')
                ->orWhere('suppliers.payment_term', 'like', '%'.$search.'%')
                ->orWhere('suppliers.tax_id', 'like', '%'.$search.'%')
                ->orWhere('suppliers.remarks', 'like', '%'.$search.'%');
        });
    }
}
