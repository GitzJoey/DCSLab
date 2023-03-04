<?php

namespace App\Models;

use App\Models\Company;
use App\Models\CustomerGroup;
use App\Models\CustomerAddress;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'is_member',
        'customer_group_id',
        'zone',
        'max_open_invoice',
        'max_outstanding_invoice',
        'max_invoice_age',
        'payment_term_type',
        'payment_term',
        'taxable_enterprise',
        'tax_id',
        'remarks',
        'status',
        'user_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customerGroup()
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function customerAddresses()
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
