<?php

namespace App\Models;

use App\Enums\PaymentTermTypeEnum;
use App\Enums\RecordStatusEnum;
use App\Traits\ScopeableByCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Customer extends Model
{
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

    protected static function booted(): void
    {
        $validateRelations = static function (self $customer): void {
            if (is_null($customer->group_id)) {
                return;
            }

            $group = CustomerGroup::find($customer->group_id);

            if (! $group || (int) $group->company_id !== (int) $customer->company_id) {
                throw new InvalidArgumentException('Customer group must exist in the same company.');
            }
        };

        static::creating(function (self $customer) use ($validateRelations) {
            $customer->ulid = Str::ulid()->generate();

            if (auth()->check()) {
                $customer->created_by = auth()->id();
                $customer->updated_by = auth()->id();
            }

            $validateRelations($customer);
        });

        static::updating(function (self $customer) use ($validateRelations) {
            if (auth()->check()) {
                $customer->updated_by = auth()->id();
            }

            $validateRelations($customer);
        });

        static::deleting(function (self $customer) {
            if (! auth()->check()) {
                return;
            }

            $customer->deleted_by = auth()->id();
            $customer->save();
        });
    }

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
