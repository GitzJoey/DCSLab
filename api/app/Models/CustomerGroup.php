<?php

namespace App\Models;

use App\Enums\PaymentTermType;
use App\Enums\RoundOn;
use App\Traits\BootableModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerGroup extends Model
{
    use BootableModel;
    use HasFactory;
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
        'round_on',
        'round_digit',
        'remarks',
    ];

    protected $casts = [
        'payment_term_type' => PaymentTermType::class,
        'round_on' => RoundOn::class,
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
