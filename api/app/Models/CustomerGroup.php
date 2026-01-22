<?php

namespace App\Models;

use App\Enums\PaymentTermTypeEnum;
use App\Enums\RoundingTypeEnum;
use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerGroup extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'code',
        'name',
        'max_open_invoice',
        'max_outstanding_invoice',
        'max_invoice_age',
        'payment_term_type',
        'payment_term',
        'selling_point',
        'selling_point_multiple',
        'sell_at_cost',
        'price_markup_percent',
        'price_markup_nominal',
        'price_markdown_percent',
        'price_markdown_nominal',
        'rounding_type',
        'rounding_digit',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'max_outstanding_invoice' => 'decimal:8',
            'payment_term_type' => PaymentTermTypeEnum::class,
            'selling_point' => 'integer',
            'selling_point_multiple' => 'decimal:8',
            'price_markup_percent' => 'decimal:8',
            'price_markup_nominal' => 'decimal:8',
            'price_markdown_percent' => 'decimal:8',
            'price_markdown_nominal' => 'decimal:8',
            'rounding_type' => RoundingTypeEnum::class,
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('customer_groups.code', 'like', '%'.$search.'%')
                ->orWhere('customer_groups.name', 'like', '%'.$search.'%')
                ->orWhere('customer_groups.remarks', 'like', '%'.$search.'%');
        });
    }
}
