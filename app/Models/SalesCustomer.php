<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\SalesCustomerGroup;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class SalesCustomer extends Model
{
    use HasFactory, LogsActivity;
    use SoftDeletes;
    
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
        'status',
    ];

    protected static $logAttributes = ['code', 'name', 'sales_customer_group_id', 'sales_territory', 'use_limit_outstanding_notes', 'limit_outstanding_notes', 'use_limit_payable_nominal', 'limit_payable_nominal', 'use_limit_due_date', 'limit_due_date', 'term', 'address', 'city', 'contact', 'tax_id', 'remarks', 'status'];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function sales_customer_group()
    {
        return $this->belongsTo(SalesCustomerGroup::class);
    }
}
