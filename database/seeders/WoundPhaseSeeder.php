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
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.'
        ]);

        WoundPhase::create([
            'name'   =>  'Inflamación',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.'
        ]);

        WoundPhase::create([
            'name'   =>  'Proliferacion',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.'
        ]);

        WoundPhase::create([
            'name'   =>  'Remodelación',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.'
        ]);
    }
}
