<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'referable_type',
        'referable_id',
        'date',
        'cash_account_id',
        'amount',
    ];

    protected $casts = [
        'date' => 'datetime',
        'amount' => 'decimal:8',
    ];

    public function referable()
    {
        return $this->morphTo();
    }

    public function cashAccount()
    {
        return $this->belongsTo(CashAccount::class);
    }
}
