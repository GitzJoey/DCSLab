<?php

namespace Database\Seeders;

use App\Actions\RandomGenerator;
use App\Models\Company;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class UnitTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run($onlyThisCompanyId = 0)
    {
        $units = [
            ['name' => 'kg', 'description' => 'kilogram (kg)', 'category' => 1],
            ['name' => 'g', 'description' => 'gram (g)', 'category' => 1],
            ['name' => 'pcs', 'description' => 'Pieces (pcs)', 'category' => 1],
            ['name' => 'pk', 'description' => 'Pack (pk)', 'category' => 1],
            ['name' => 'dz', 'description' => 'Dozen (dz)', 'category' => 1],
        ];

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

        foreach ($companies as $c) {
            foreach ($units as $u) {
                $newUnit = new Unit();
                $newUnit->company_id = $c;
                $newUnit->code = (new RandomGenerator())->generateFixedLengthNumber(5);
                $newUnit->name = $u['name'];
                $newUnit->description = $u['description'];
                $newUnit->category = (new RandomGenerator())->generateNumber(1, 3);

                $newUnit->save();
            }
        }
    }
}
