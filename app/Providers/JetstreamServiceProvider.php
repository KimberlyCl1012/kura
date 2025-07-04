<?php

namespace App\Providers;

use App\Actions\Jetstream\AddTeamMember;
use App\Actions\Jetstream\CreateTeam;
use App\Actions\Jetstream\DeleteTeam;
use App\Actions\Jetstream\DeleteUser;
use App\Actions\Jetstream\InviteTeamMember;
use App\Actions\Jetstream\RemoveTeamMember;
use App\Actions\Jetstream\UpdateTeamName;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Jetstream;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePermissions();

        Jetstream::createTeamsUsing(CreateTeam::class);
        Jetstream::updateTeamNamesUsing(UpdateTeamName::class);
        Jetstream::addTeamMembersUsing(AddTeamMember::class);
        Jetstream::inviteTeamMembersUsing(InviteTeamMember::class);
        Jetstream::removeTeamMembersUsing(RemoveTeamMember::class);
        Jetstream::deleteTeamsUsing(DeleteTeam::class);
        Jetstream::deleteUsersUsing(DeleteUser::class);
    }

    /**
     * Configure the roles and permissions that are available within the application.
     */
    protected function configurePermissions(): void
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::role('admin', 'Administrator', [
            'create',
            'read',
            'update',
            'delete',
            'editor:edit-all', 
        ])->description('Los usuarios administradores pueden realizar cualquier acción.');

        Jetstream::role('admin_clinico', 'Administrador Clínico', [
            'read',
            'update',
        ])->description('Los usuarios editores tienen la capacidad de leer y actualizar.');

        Jetstream::role('sup_clinico', 'Supervisor Clínico', [
            'read',
            'update',
        ])->description('Los usuarios editores tienen la capacidad de leer y actualizar.');

        Jetstream::role('pro_clinico', 'Profesional Clínico', [
            'read'
        ])->description('Los usuarios lectores tienen la capacidad de leer.');

        Jetstream::role('usuario', 'Usuario', [
            'read'
        ])->description('Los usuarios lectores tienen la capacidad de leer.');
    }
}
