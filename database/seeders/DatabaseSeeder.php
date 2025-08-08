<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(StateSeeder::class);
        $this->call(AddressSeeder::class);
        $this->call(CompanySeeder::class);
        $this->call(SiteSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(BodyLocationSeeder::class);
        $this->call(BodySublocationSeeder::class);
        $this->call(HealthInstitutionSeeder::class);
        $this->call(TreatmentMethodSeeder::class);
        $this->call(TreatmentSubmethodSeeder::class);
        $this->call(WoundPhaseSeeder::class);
        $this->call(WoundTypeSeeder::class);
        $this->call(WoundSubtypeSeeder::class);
    }
}
