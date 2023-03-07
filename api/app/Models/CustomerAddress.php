<?php

namespace App\Models;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    protected $table = 'customer_addresses';

    protected $fillable = [
        'company_id',
        'customer_id',
        'address',
        'city',
        'contact',
        'is_main',
        'remarks',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
