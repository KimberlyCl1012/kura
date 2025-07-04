<?php

namespace Database\Seeders;

use App\Models\Address;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
  {
        Address::create([
            'type'  => 'local',
            'streetAddress'  => 'Iztaccihuatl 10',
            'addressLine2'  => 'Florida, Álvaro Obregón',
            'city'  => 'CDMX',
            'state_id'  =>  1,
            'postalCode'  => '01030',
            'country'  => 'MX',
            'latitude'  => '19.361476336406774',
            'longitude'  => '-99.18239922883637',
            'state'     => 1
        ]);
    }
}
