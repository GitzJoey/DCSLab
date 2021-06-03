<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class SalesCustomerGroup extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'name',
        'is_member_card',
        'use_limit_outstanding_notes',
        'limit_outstanding_notes',
        'use_limit_payable_nominal',
        'limit_payable_nominal',
        'use_limit_due_date',
        'limit_due_date',
        'term',
        'selling_point',
        'selling_point_multiple',
        'sell_at_capital_price',
        'global_markup_percent',
        'global_markup_nominal',
        'global_discount_percent',
        'global_discount_nominal',
        'is_rounding',
        'round_on',
        'round_digit',
        'remarks',
        'finance_cash_id'

    ];

    protected static $logAttributes = ['code', 'name', 'is_member_card', 'use_limit_outstanding_notes', 'limit_outstanding_notes', 'use_limit_payable_nominal', 'limit_payable_nominal', 'use_limit_due_date', 'limit_due_date', 'term', 'selling_point', 'selling_point_multiple', 'sell_at_capital_price', 'global_markup_percent', 'global_markup_nominal', 'global_discount_percent', 'global_discount_nominal', 'is_rounding', 'round_on', 'round_digit', 'remarks', 'finance_cash_id'];

    protected static $logOnlyDirty = true;

    protected $hidden = [
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
