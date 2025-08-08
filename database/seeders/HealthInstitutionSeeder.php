<?php

namespace Database\Seeders;

use App\Models\HealthInstitution;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HealthInstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HealthInstitution::create([
            'name'  => 'Instituto Mexicano del Seguro Social (IMSS)',
            'description'   => '',
            'state'     => 1
        ]);

        HealthInstitution::create([
            'name'  => 'Instituto de Seguridad y Servicios Sociales de los Trabajadores del Estado (ISSSTE)',
            'description'   => '',
            'state'     => 1
        ]);

        HealthInstitution::create([
            'name'  => 'Instituto Mexicano del Seguro Social (IMSS Bienestar) ​',
            'description'   => '',
            'state'     => 1
        ]);

        HealthInstitution::create([
            'name'  => 'Secretaría de Salud de México (SSA)',
            'description'   => '',
            'state'     => 1
        ]);

        HealthInstitution::create([
            'name'  => 'Otra',
            'description'   => '',
            'state'     => 1
        ]);
    }
}
