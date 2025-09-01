<?php

namespace Database\Seeders;

use App\Models\Permission;
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
            'created_by' => 2,
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
            'created_by' => 3,
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
            'created_by' => 4,
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
            'created_by' => 2,
            'state' => 1,
        ]);

        $teamAdmin = Team::create([
            'user_id'       => $userOne->id,
            'name'          => 'admin',
            'description'   => 'Administrador',
            'personal_team' => true,
        ]);

        $teamKura = Team::create([
            'user_id'       => $userOne->id,
            'name'          => 'admin_kura',
            'description'   => 'Administrador Kura+',
            'personal_team' => false,
        ]);

        $teamRespSitio = Team::create([
            'user_id'       => $userOne->id,
            'name'          => 'resp_sitio',
            'description'   => 'Resposable del sitio',
            'personal_team' => false,
        ]);

        $teamOperativo = Team::create([
            'user_id'       => $userOne->id,
            'name'          => 'perfil_operativo',
            'description'   => 'Perfil operativo',
            'personal_team' => false,
        ]);

        $roles = config('roles', []);

        foreach ($roles as $roleKey => $cfg) {
            $team = Team::where('name', $roleKey)->first();
            if (! $team) {
                continue;
            }

            $slugs = $cfg['permissions'] ?? [];
            $permissionIds = Permission::whereIn('slug', $slugs)->pluck('id')->all();

            $this->syncTeamPermissions($team, $permissionIds);
        }

        // Asociar el equipo al usuario
        $userOne->currentTeam()->associate($teamAdmin);
        $userOne->save();

        // Asignar el rol al usuario en su equipo
        $userOne->switchTeam($teamAdmin); // Cambiar al equipo adecuado
        $userOne->currentTeam->users()->attach($userOne->id, ['role' => 'admin']); // Asignar el rol

        $userTwo->currentTeam()->associate($teamKura);
        $userTwo->save();

        $userTwo->switchTeam($teamKura);
        $userTwo->currentTeam->users()->attach($userTwo->id, ['role' => 'admin_kura']);

        $userThree->currentTeam()->associate($teamOperativo);
        $userThree->save();

        $userThree->switchTeam($teamOperativo);
        $userThree->currentTeam->users()->attach($userThree->id, ['role' => 'perfil_operativo']);

        $userFour->currentTeam()->associate($teamRespSitio);
        $userFour->save();

        $userFour->switchTeam($teamRespSitio);
        $userFour->currentTeam->users()->attach($userFour->id, ['role' => 'resp_sitio']);
    }

    protected function syncTeamPermissions(Team $team, array $permissionIds): void
    {
        if (method_exists($team, 'permissions')) {
            $team->permissions()->sync($permissionIds);
            return;
        }

        DB::table('team_permissions')->where('team_id', $team->id)->delete();

        if (!empty($permissionIds)) {
            $now = now();
            $rows = array_map(fn($pid) => [
                'team_id'       => $team->id,
                'permission_id' => $pid,
                'created_at'    => $now,
                'updated_at'    => $now,
            ], $permissionIds);

            DB::table('team_permissions')->insert($rows);
        }
    }
}
