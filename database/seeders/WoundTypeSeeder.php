<?php

namespace Database\Seeders;

use App\Models\WoundType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WoundTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
 {
        WoundType::create([
            'name'   =>  'Linfovascular',
            'description'  => '',
            'state'   => 1
        ]);

        WoundType::create([
            'name'   =>  'Trauma',
            'description'  => '',
            'state'   => 1
        ]);

        WoundType::create([
            'name'   =>  'Lesion por presión',
            'description'  => '',
            'state'  => 1
        ]);

        WoundType::create([
            'name'   =>  'Quirurgico',
            'description'  => '',
            'state'  => 1
        ]);

        WoundType::create([
            'name'   =>  'Quemadura',
            'description'  => '',
            'state'   => 1
        ]);

        WoundType::create([
            'name'   =>  'Infección',
            'description'  => '',
            'state'   => 1
        ]);

        WoundType::create([
            'name'   =>  'Auto-inmunes',
            'description'  => '',
            'state'   => 1
        ]);

        WoundType::create([
            'name'   =>  'Pie Diabético',
            'description'  => '',
            'state'   => 1
        ]);

        WoundType::create([
            'name'   =>  'Otra',
            'description'  => '',
            'state'   => 1
        ]);
    }
}
