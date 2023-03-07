<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeAccess extends Model
{
    protected $table = 'employee_accesses';

    protected $fillable = [
        'employee_id',
        'branch_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
