<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrepaidExpenseImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'prepaid_expense_id',
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

    public function prepaidExpense()
    {
        return $this->belongsTo(PrepaidExpense::class)->withTrashed();
    }
}
