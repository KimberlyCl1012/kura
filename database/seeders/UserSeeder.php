<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userOne = User::create([
            'name' => 'Administrator',
            'email' => 'admin@mail.com',
            'password' => bcrypt('kura+123'),
        ]);

        $userTwo = User::create([
            'name' => 'Claudia',
            'email' => 'claudia.diaz@kuramas.com',
            'password' => bcrypt('kura+123'),
        ]);

        DB::table('user_details')->insert([
            'user_id'  => 2,
            'company_id'  => 1,
            'site_id'  => 1,
            'sex'  => 'Mujer',
            'name' => 'Claudia',
            'fatherLastName' => 'Diaz',
            'motherLastName' => 'Olvera',
            'mobile' => '8754214587',
            'contactEmail' => 'claudia.diaz@kuramas.com'
        ]);

        $userThree = User::create([
            'name' => 'Ivonne',
            'email' => 'ivonne.vargas@kuramas.com',
            'password' => bcrypt('kura+123'),
        ]);

        DB::table('user_details')->insert([
            'user_id'  => 3,
            'company_id'  => 1,
            'site_id'  => 1,
            'sex'  => 'Mujer',
            'name' => 'Ivonne',
            'fatherLastName' => 'Vargas',
            'motherLastName' => 'Casiano',
            'mobile' => '8754214587',
            'contactEmail' => 'ivonne.vargas@kuramas.com'
        ]);

        $userFour = User::create([
            'name' => 'Carlos',
            'email' => 'carlosangelm@procomsa.com.mx',
            'password' => bcrypt('kura+123'),
        ]);

        DB::table('user_details')->insert([
            'user_id'  => 4,
            'company_id'  => 1,
            'site_id'  => 1,
            'sex'  => 'Hombre',
            'name' => 'Carlos Angel',
            'fatherLastName' => 'Martinez',
            'motherLastName' => '',
            'mobile' => '8754214587',
            'contactEmail' => 'carlosangelm@procomsa.com.mx'
        ]);

        $userFive = User::create([
            'name' => 'Juan',
            'email' => 'juan@mail.com',
            'password' => bcrypt('kura+123'),
        ]);

        DB::table('user_details')->insert([
            'user_id'  => 5,
            'company_id'  => 1,
            'site_id'  => 1,
            'sex'  => 'Hombre',
            'name' => 'Juan',
            'fatherLastName' => 'Perez',
            'motherLastName' => 'Lopez',
            'mobile' => '8754214587',
            'contactEmail' => 'juan@mail.com'
        ]);

        DB::table('kurators')->insert([
            'id' => 1,
            'user_uuid' => 'K2024-2323',
            'user_detail_id' => 1,
            'specialty' => 'General',
            'type_kurator' => 'Médico',
            'type_identification' => 'INE',
            'identification' => '54875454',
            'state' => 1
        ]);

        DB::table('kurators')->insert([
            'id' => 2,
            'user_uuid' => 'K2024-2324',
            'user_detail_id' => 2,
            'specialty' => 'General',
            'type_kurator' => 'Médico',
            'type_identification' => 'INE',
            'identification' => '54875454',
            'state' => 1
        ]);

        DB::table('kurators')->insert([
            'id' => 3,
            'user_uuid' => 'K2024-2325',
            'user_detail_id' => 3,
            'specialty' => 'General',
            'type_kurator' => 'Médico',
            'type_identification' => 'INE',
            'identification' => '54875454',
            'state' => 1
        ]);

        DB::table('patients')->insert([
            'id' => 1,
            'user_uuid' => 'P2024-8434',
            'state_id' => 15,
            'user_detail_id' => 4,
            'dateOfBirth' => '1994-04-04',
            'type_identification' => 'INE',
            'identification' => '55258555',
            'streetAddress' => 'san jeronimo 214',
            'city' => 'Ciudad de México',
            'postalCode' => '40052',
            'relativeName' => null,
            'kinship' => null,
            'relativeMobile' => null,
            'consent' => 1,
            'state' => 1,
        ]);

        // Crear el equipo (si es el personal)
        $teamOne = Team::create([
            'user_id' => $userOne->id,
            'name' => 'admin',
            'personal_team' => true,
        ]);

        $teamTwo = Team::create([
            'user_id' => $userOne->id,
            'name' => 'admin_clinico',
            'personal_team' => true,
        ]);


        $teamThree = Team::create([
            'user_id' => $userOne->id,
            'name' => 'sup_clinico',
            'personal_team' => true,
        ]);


        $teamFour = Team::create([
            'user_id' => $userOne->id,
            'name' => 'pro_clinico',
            'personal_team' => true,
        ]);

        $teamFive = Team::create([
            'user_id' => $userOne->id,
            'name' => 'usuario',
            'personal_team' => true,
        ]);

        // Asociar el equipo al usuario
        $userOne->currentTeam()->associate($teamOne);
        $userOne->save();

        // $userTwo->currentTeam()->associate($team);
        // $userTwo->save();

        // $userThree->currentTeam()->associate($team);
        // $userThree->save();

        // $userFour->currentTeam()->associate($team);
        // $userFour->save();

        // Asignar el rol al usuario en su equipo
        $userOne->switchTeam($teamOne); // Cambiar al equipo adecuado
        $userOne->currentTeam->users()->attach($userOne->id, ['role' => 'admin']); // Asignar el rol

        // $userTwo->switchTeam($team); // Cambiar al equipo adecuado
        // $userTwo->currentTeam->users()->attach($userTwo->id, ['role' => 'administrator']); // Asignar el rol

        // $userThree->switchTeam($team); // Cambiar al equipo adecuado
        // $userThree->currentTeam->users()->attach($userThree->id, ['role' => 'administrator']); // Asignar el rol

        // $userFour->switchTeam($team); // Cambiar al equipo adecuado
        // $userFour->currentTeam->users()->attach($userFour->id, ['role' => 'administrator']); // Asignar el rol

        // Si deseas asignar otro rol, puedes hacerlo de la siguiente manera:
        // $userThree->currentTeam->users()->attach($userThree->id, ['role' => 'editor']);

    }
}
