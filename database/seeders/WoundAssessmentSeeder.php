<?php

namespace Database\Seeders;

use App\Models\WoundAssessment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WoundAssessmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $data = [
            // Edema
            ['type' => 'Edema', 'name' => '+++'],
            ['type' => 'Edema', 'name' => '++'],
            ['type' => 'Edema', 'name' => '+'],
            ['type' => 'Edema', 'name' => 'No aplica'],

            // Dolor
            ['type' => 'Dolor', 'name' => 'En reposo'],
            ['type' => 'Dolor', 'name' => 'Con movimiento'],
            ['type' => 'Dolor', 'name' => 'Ninguno'],

            // Tipo dolor
            ['type' => 'Tipo de dolor', 'name' => 'Nociceptivo'],
            ['type' => 'Tipo de dolor', 'name' => 'Neuropático'],

            // Duración del dolor
            ['type' => 'Duración del dolor', 'name' => 'Antes de la curación'],
            ['type' => 'Duración del dolor', 'name' => 'Despues de la curación'],
            ['type' => 'Duración del dolor', 'name' => 'Posterior a la curación'],


            // Exudado cantidad
            ['type' => 'Exudado (Cantidad)', 'name' => 'Abundante'],
            ['type' => 'Exudado (Cantidad)', 'name' => 'Moderado'],
            ['type' => 'Exudado (Cantidad)', 'name' => 'Bajo'],

            // Exudado tipo
            ['type' => 'Exudado (tipo)', 'name' => 'Seroso'],
            ['type' => 'Exudado (tipo)', 'name' => 'Purulento'],
            ['type' => 'Exudado (tipo)', 'name' => 'Hemático'],
            ['type' => 'Exudado (tipo)', 'name' => 'Serohemático'],

            // Olor
            ['type' => 'Olor', 'name' => 'Mal olor'],
            ['type' => 'Olor', 'name' => 'No aplica'],

            // Bordes
            ['type' => 'Borde de la herida', 'name' => 'Adherido'],
            ['type' => 'Borde de la herida', 'name' => 'No adherido'],
            ['type' => 'Borde de la herida', 'name' => 'Enrollado'],
            ['type' => 'Borde de la herida', 'name' => 'Epitelizado'],

            // Piel perilesional
            ['type' => 'Piel perilesional', 'name' => 'Eritema'],
            ['type' => 'Piel perilesional', 'name' => 'Escoriación'],
            ['type' => 'Piel perilesional', 'name' => 'Maceración'],
            ['type' => 'Piel perilesional', 'name' => 'Reseca'],
            ['type' => 'Piel perilesional', 'name' => 'Equimosis'],
            ['type' => 'Piel perilesional', 'name' => 'Indurada'],
            ['type' => 'Piel perilesional', 'name' => 'Queratosis'],
            ['type' => 'Piel perilesional', 'name' => 'Integra'],
            ['type' => 'Piel perilesional', 'name' => 'Hiperpigmentada'],

            // Infección
            ['type' => 'Infeccion', 'name' => 'Celulitis'],
            ['type' => 'Infeccion', 'name' => 'Pirexia'],
            ['type' => 'Infeccion', 'name' => 'Aumento del dolor'],
            ['type' => 'Infeccion', 'name' => 'Rápida extensión del área ulcerada'],
            ['type' => 'Infeccion', 'name' => 'Mal olor'],
            ['type' => 'Infeccion', 'name' => 'Incremento del exudado'],
            ['type' => 'Infeccion', 'name' => 'Eritema'],
            ['type' => 'Infeccion', 'name' => 'No aplica'],
        ];

        foreach ($data as $item) {
            WoundAssessment::firstOrCreate($item);
        }
    }
}
