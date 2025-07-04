<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Site::create([
            'address_id'  => 1,
            'siteName' =>  'Kura+',
            'phone'  =>  '525512345678',
            'email_admin'  => 'claudia.diaz@kuramas.com',
            'description' => 'Somos Kura+ S.A. de C.V.',
            'state' => 1
        ]);

        Site::create([
            'address_id'  => 1,
            'siteName' =>  'Procomsa',
            'phone'  =>  '5556894541',
            'email_admin'  => 'procomsa@procomsa.com',
            'description' => 'Av. San Jerónimo 424, Jardines del Pedregal, Álvaro Obregón, 01900',
            'state' => 1
        ]);
    }
}
