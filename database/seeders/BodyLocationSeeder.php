<?php

namespace Database\Seeders;

use App\Models\BodyLocation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BodyLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BodyLocation::create([
            'name'  =>   'Cabeza',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Tórax',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Brazo derecho',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Brazo izquierdo',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Codo',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Antebrazo izquierdo',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Antebrazo derecho',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Mano izquierda',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Dedos mano izquierda',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Mano derecha',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Dedos mano derecha',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Abdomen',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Zona genital femenino',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Zona genital masculino',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Espalda',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Espalda baja',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Zona sacrococcigea',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Pierna izquierda',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Pierna derecha',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Rodilla izquierda',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Rodilla derecha',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Espinilla izquierda',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Espinilla derecha',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Pantorrilla izquierda',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Pantorrilla derecha',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Pie izquierdo',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Pie derecho',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Dedos pie izquierdo',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Dedos pie derecho',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Zona plantar izquierda',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Zona plantar derecha',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Espacios interdigitales izquierdo',
            'description'  => '',
            'state'     =>   1
        ]);

        BodyLocation::create([
            'name'  =>   'Espacios interdigitales derecho',
            'description'  => '',
            'state'     =>   1
        ]);
    }
}
