<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Models\Company;
use App\Models\Profile;
use App\Models\Setting;
use App\Enums\UserRoles;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use App\Actions\RandomGenerator;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\CompanyTableSeeder;
use Illuminate\Database\Eloquent\Collection;

class EmployeeTableSeeder extends Seeder
{
    public function run($employeePerCompanies = 3, $onlyThisCompanyId = 0)
    {
        if (Company::count() < 2) {
            $seed_company = new CompanyTableSeeder();
            $seed_company->callWith(CompanyTableSeeder::class, [2]);
        }

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

        $faker = \Faker\Factory::create('id_ID');

        foreach($companies as $c) {
            for($i = 0; $i < $employeePerCompanies; $i++)
            {
                $name = $faker->name;
                $email = $faker->email;

                $user = new User();
                $user->name = $name;
                $user->email = $email;
    
                if (empty($password)) {
                    $user->password = (new RandomGenerator())->generateAlphaNumeric(5);
                    $user->password_changed_at = null;
                } else {
                    $user->password = Hash::make($password);
                    $user->password_changed_at = Carbon::now();
                }
    
                $user->save();

                $first_name = '';
                $last_name = '';
                if ($name == trim($name) && strpos($name, ' ') !== false) {
                    $pieces = explode(" ", $name);
                    $first_name = $pieces[0];
                    $last_name = $pieces[1];
                } else {
                    $first_name = $name;
                }
    
                $profile = new Profile();
    
                $profile->first_name = $first_name;
                $profile->last_name = $last_name;
                $profile->address = $faker->address;
                $profile->city = $faker->city;
                $profile->postal_code = $faker->postcode;
                $profile->country = $faker->country;
                $profile->tax_id = (new RandomGenerator())->generateNumber(10000000, 999999999);
                $profile->ic_num = (new RandomGenerator())->generateNumber(10000000, 999999999);
                $profile->status = (new RandomGenerator())->generateNumber(0, 1);
                $profile->img_path = null;
                $profile->remarks = $faker->sentence;
    
                $user->profile()->save($profile);
    
                $settings = array (
                    new Setting(array(
                        'type' => 'KEY_VALUE',
                        'key' => 'PREFS.THEME',
                        'value' => 'side-menu-light-full',
                    )),
                    new Setting(array(
                        'type' => 'KEY_VALUE',
                        'key' => 'PREFS.DATE_FORMAT',
                        'value' => 'yyyy_MM_dd',
                    )),
                    new Setting(array(
                        'type' => 'KEY_VALUE',
                        'key' => 'PREFS.TIME_FORMAT',
                        'value' => 'hh_mm_ss',
                    )),
                );
                $user->settings()->saveMany($settings);
    
                $rolesId = array(Role::where('name', UserRoles::USER->value)->first()->id);
                $user->attachRoles($rolesId);
    
                if (env('AUTO_VERIFY_EMAIL', true))
                    $user->markEmailAsVerified();
    
                $companyId = Company::inRandomOrder()->first()->id;
                $user->companies()->attach([$companyId]);
                $userId = $user->id;

                $employee = Employee::factory()->make([
                    'company_id' => $companyId,
                    'user_id' => $userId,
                ]);
    
                $employee->save();
            }
        }
    }
}
