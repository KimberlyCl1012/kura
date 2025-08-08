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
            'description'  => '',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Limpieza de la herida',
            'description'  => '',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Antisépticos',
            'description'  => '',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Protección de la piel',
            'description'  => '',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Apósito',
            'description'  => '',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Tratamiento para la infección',
            'description'  => '',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Vendaje - Sistema de compresión',
            'description'  => '',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Descarga',
            'description'  => '',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Complementos nutricionales',
            'description'  => '',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Tratamiento avanzado',
            'description'  => '',
            'state'   => 1
        ]);

        Method::create([
            'name'   =>  'Rehabilitación',
            'description'  => '',
            'state'   => 1
        ]);
    }
}
