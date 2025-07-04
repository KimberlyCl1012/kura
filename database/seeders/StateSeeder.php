<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
  {
        State::create([
            'name'  =>  'Aguascalientes',
            'clave' => 'AS',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Baja California',
            'clave' => 'BC',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Baja California Sur',
            'clave' => 'BS',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Campeche',
            'clave' => 'CC',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Chiapas',
            'clave' => 'CS',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Chihuahua',
            'clave' => 'CH',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Coahuila',
            'clave' => 'CL',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Colima',
            'clave' => 'CM',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Ciudad de México',
            'clave' => 'CDMX',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Durango',
            'clave' => 'DG',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Guanajuato',
            'clave' => 'GT',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Guerrero',
            'clave' => 'GR',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Hidalgo',
            'clave' => 'HG',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Jalisco',
            'clave' => 'JC',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'México',
            'clave' => 'MX',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Michoacán',
            'clave' => 'MN',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Morelos',
            'clave' => 'MS',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Nayarit',
            'clave' => 'NT',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Nuevo León',
            'clave' => 'NL',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Oaxaca',
            'clave' => 'OC',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Puebla',
            'clave' => 'PL',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Queretaro',
            'clave' => 'QT',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Quintana Roo',
            'clave' => 'QR',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'San Luis Patosí',
            'clave' => 'SP',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Sinaloa',
            'clave' => 'SL',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Sonora',
            'clave' => 'SR',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Tabasco',
            'clave' => 'TC',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Tamaulipas',
            'clave' => 'TS',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Tlaxcala',
            'clave' => 'TL',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Veracruz',
            'clave' => 'VZ',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Yucatán',
            'clave' => 'YN',
            'state' => 1
        ]);

        State::create([
            'name'  =>  'Zacatecas',
            'clave' => 'ZS',
            'state' => 1
        ]);
    }
}
