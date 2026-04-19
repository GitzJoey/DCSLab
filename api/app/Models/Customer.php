<?php

namespace App\Models;

use App\Enums\PaymentTermTypeEnum;
use App\Enums\RecordStatusEnum;
use App\Traits\BootableModel;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use BootableModel;
    use HasFactory;
    use ScopeableByCompany;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'user_id',
        'code',
        'is_member',
        'name',
        'group_id',
        'zone',
        'max_open_invoice',
        'max_outstanding_invoice',
        'max_invoice_age',
        'payment_term_type',
        'payment_term',
        'taxable_enterprise',
        'tax_id',
        'status',
        'remarks',
    ];

    protected $casts = [
        'is_member' => 'boolean',
        'max_open_invoice' => 'integer',
        'max_outstanding_invoice' => 'decimal:8',
        'max_invoice_age' => 'integer',
        'payment_term_type' => PaymentTermTypeEnum::class,
        'taxable_enterprise' => 'boolean',
        'status' => RecordStatusEnum::class,
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function group()
    {
        return $this->belongsTo(CustomerGroup::class, 'group_id')->withTrashed();
    }

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }
}
