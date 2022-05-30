<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Services\RoleService;
use Illuminate\Database\Seeder;
use App\Services\EmployeeService;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Collection;

class EmployeeTableSeeder extends Seeder
{
    public function run($employeePerCompanies = 3, $onlyThisCompanyId = 0)
    {
        if ($onlyThisCompanyId != 0) {
            $c = Company::find($onlyThisCompanyId);

            if ($c) {
                $companies = (new Collection())->push($c->id);
            } else {
                $companies = Company::get()->pluck('id');
            }
        } else {
            $companies = Company::get()->pluck('id');
        }

        foreach($companies as $c) {
            for($i = 0; $i < $employeePerCompanies; $i++)

            $employee = Employee::factory()->make([]);

            {
                $companyId = Company::inRandomOrder()->first()->id;
            
                    $name = $employee->name;
                    
                    $first_name = '';
                    $last_name = '';
                    if ($name == trim($name) && strpos($name, ' ') !== false) {
                        $pieces = explode(" ", $name);
                        $first_name = $pieces[0];
                        $last_name = $pieces[1];
                    } else {
                        $first_name = $name;
                    }
        
                    $container = Container::getInstance();
                    $roleService = $container->make(RoleService::class);
                    $rolesId = [];
                    array_push($rolesId, $roleService->readBy('NAME', 'user')->id);
        
                    $address = $employee->address;
                    $city = $employee->city;
                    $postalCode = $employee->postcode;
                    $country = $employee->country;
                    $taxId = $employee->tax_id;
                    $icNum = $employee->ic_num;
                    $remarks = $employee->sentence;
                    $status = $employee->status;
                    $profile = array (
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'address' => $address,
                        'city' => $city,
                        'postal_code' => $postalCode,
                        'country' => $country,
                        'tax_id' => $taxId,
                        'ic_num' => $icNum,
                        'remarks' => $remarks,
                        'status' => $status,
                    );
        
                $email = $employee->email;
                $user = [];
                array_push($user, array (
                    'name' => $name,
                    'email' => $email,
                    'password' => '',
                    'rolesId' => $rolesId,
                    'profile' => $profile
                ));
        
                $joinDate = $employee->join_date;
        
                $employeeService = $container->make(EmployeeService::class);
                $employeeService->create(
                    company_id: $companyId,
                    user: $user,
                    join_date: $joinDate,
                    status: $status
                );
            }
        }
    }
}
