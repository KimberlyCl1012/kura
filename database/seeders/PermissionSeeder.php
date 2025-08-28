<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create([
            'name' => 'Crear paciente',
            'slug' => 'create_patient',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Editar paciente',
            'slug' => 'edit_patient',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Ver paciente',
            'slug' => 'show_patient',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Eliminar paciente',
            'slug' => 'delete_patient',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Crear expediente',
            'slug' => 'create_medical_record',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Editar expediente',
            'slug' => 'edit_medical_record',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Ver expediente',
            'slug' => 'show_medical_record',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Eliminar expediente',
            'slug' => 'delete_medical_record',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Crear evidencia fotografica',
            'slug' => 'create_photographic_evidence',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Editar evidencia fotografica',
            'slug' => 'edit_photographic_evidence',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Ver evidencia fotografica',
            'slug' => 'show_photographic_evidence',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Eliminar evidencia fotografica',
            'slug' => 'delete_photographic_evidence',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Crear personal clínico',
            'slug' => 'create_clinical_staff',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Editar personal clínico',
            'slug' => 'edit_clinical_staff',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Ver personal clínico',
            'slug' => 'show_clinical_staff',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Eliminar personal clínico',
            'slug' => 'delete_clinical_staff',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Crear notas',
            'slug' => 'create_notes',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Editar notas',
            'slug' => 'edit_notes',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Ver notas',
            'slug' => 'show_notes',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Eliminar notas',
            'slug' => 'delete_notes',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Crear consulta',
            'slug' => 'create_appointment',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Ver consultas',
            'slug' => 'show_appointments',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Eliminar consulta',
            'slug' => 'delete_appointment',
            'description'  => '',
        ]);

        Permission::create([
            'name' => 'Reasignar paciente',
            'slug' => 'reassign_patient',
            'description'  => '',
        ]);
    }
}
