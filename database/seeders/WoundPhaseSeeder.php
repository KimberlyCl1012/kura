<?php

namespace Database\Seeders;

use App\Models\WoundPhase;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WoundPhaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
  {
        WoundPhase::create([
            'name'   =>  'Hemostasia',
            'description'  => '',
        ]);

        WoundPhase::create([
            'name'   =>  'Inflamación',
            'description'  => '',
        ]);

        WoundPhase::create([
            'name'   =>  'Proliferacion',
            'description'  => '',
        ]);

        WoundPhase::create([
            'name'   =>  'Remodelación',
            'description'  => '',
        ]);
    }
}
