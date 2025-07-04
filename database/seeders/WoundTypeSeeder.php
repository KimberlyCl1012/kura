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
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'   => 1
        ]);

        WoundType::create([
            'name'   =>  'Trauma',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'   => 1
        ]);

        WoundType::create([
            'name'   =>  'Lesion por presión',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'  => 1
        ]);

        WoundType::create([
            'name'   =>  'Quirurgico',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'  => 1
        ]);

        WoundType::create([
            'name'   =>  'Tërmica',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'   => 1
        ]);

        WoundType::create([
            'name'   =>  'Infección',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'   => 1
        ]);

        WoundType::create([
            'name'   =>  'Auto-inmunes',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'   => 1
        ]);

        WoundType::create([
            'name'   =>  'Pie Diabético',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'   => 1
        ]);

        WoundType::create([
            'name'   =>  'Otra',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'   => 1
        ]);
    }
}
