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
            'name'  => 'Secretaría de Salud',
            'description'   => '',
            'state'     => 1
        ]);

        HealthInstitution::create([
            'name'  => 'Instituto Mexicano del Seguro Social (IMSS)',
            'description'   => '',
            'state'     => 1
        ]);

        HealthInstitution::create([
            'name'  => 'Instituto Nacional de Cancerología (INCAN)',
            'description'   => '',
            'state'     => 1
        ]);

        HealthInstitution::create([
            'name'  => 'Instituto Nacional de Pediatría (INP)',
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
