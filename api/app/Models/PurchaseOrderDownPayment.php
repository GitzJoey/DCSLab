<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDownPayment extends Model
{

    protected $fillable = [
        'company_id',
        'branch_id',
        'purchase_order_id',
        'coa_cash_and_bank_id',
        'payment_code',
        'date',
        'payment_term',
        'amount',
        'remarks',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function coaCashAndBank()
    {
        return $this->belongsTo(ChartOfAccount::class)->withTrashed();
    }
}
