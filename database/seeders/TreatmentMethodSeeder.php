<?php

namespace Database\Seeders;

use App\Models\Method;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TreatmentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
 {
        Method::create([
            'name'   =>  'Desbridamiento',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Limpieza de la herida',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Antisépticos',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'   => 1
        ]);
        
        Method::create([
            'name'   =>  'Protección de la piel',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Aposito primario',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Tratamiento para la infección',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Descarga',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Complementos nutricionales',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Tratamiento avanzado',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Rehabilitación',
            'description'  => 'Hay muchas variaciones de los pasajes de Lorem Ipsum disponibles, pero la mayoría sufrió alteraciones en alguna manera, ya sea porque se le agregó humor, o palabras aleatorias que no parecen ni un poco creíbles.',
            'state'   => 1
        ]);

    }
}
