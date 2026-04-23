<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'expense_id',
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

    public function expense()
    {
        return $this->belongsTo(Expense::class)->withTrashed();
    }
}
