<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class SalesCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'sales_customer_group_id',
        'sales_territory',
        'use_limit_outstanding_notes',
        'limit_outstanding_notes',
        'use_limit_payable_nominal',
        'limit_payable_nominal',
        'use_limit_due_date',
        'limit_due_date',
        'term',
        'address',
        'city',
        'contact',
        'tax_id',
        'remarks',
        'is_active',
    ];

    protected $hidden = [
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
