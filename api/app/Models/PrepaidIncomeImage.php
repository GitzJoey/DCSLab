<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrepaidIncomeImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'prepaid_income_id',
        'path',
        'hash',
        'is_main',
    ];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
        ];
    }

    public function prepaidIncome()
    {
        return $this->belongsTo(PrepaidIncome::class)->withTrashed();
    }
}
