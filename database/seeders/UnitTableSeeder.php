<?php

namespace Database\Seeders;

use App\Actions\RandomGenerator;
use App\Models\Company;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class UnitTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $units = [
            ['name' => 'kg', 'description' => 'kilogram (kg)'],
            ['name' => 'g', 'description' => 'gram (g)']
        ];

        $companies = Company::get()->pluck('id');

        foreach($companies as $c) {
            foreach ($units as $u) {
                $newUnit = new Unit();
                $newUnit->company_id = $c;
                $newUnit->code = (new RandomGenerator())->generateFixedLengthNumber(5);
                $newUnit->name = $u['name'];
                $newUnit->description = $u['description'];

                $newUnit->save();
            }
        }
    }
}
