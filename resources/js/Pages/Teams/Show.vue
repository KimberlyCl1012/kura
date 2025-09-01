<script setup>
import AppLayout from '../../Layouts/sakai/AppLayout.vue';
import DeleteTeamForm from '@/Pages/Teams/Partials/DeleteTeamForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TeamMemberManager from '@/Pages/Teams/Partials/TeamMemberManager.vue';
import UpdateTeamNameForm from '@/Pages/Teams/Partials/UpdateTeamNameForm.vue';

defineProps({
    team: Object,
    availableRoles: Array,
    allUserTeamRows: { type: Array, default: () => [] },
    teams: Array,
    users: Array,
    availableRoles: Array,
    permissions: Object,
});

</script>

<template>
    <AppLayout title="Rol confguración">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Rol configuración
            </h2>
        </template>

        <div style="background-color:white">
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

                <UpdateTeamNameForm :team="team" :permissions="permissions" />

                <TeamMemberManager class="mt-10 sm:mt-0" :team="team" :users="users" :available-roles="availableRoles"
                    :role_teams="teams" :user-permissions="permissions" />

                <template v-if="permissions.canDeleteTeam && !team.personal_team">
                    <SectionBorder />

                    <DeleteTeamForm class="mt-10 sm:mt-0" :team="team" />
                </template>

                <div>
                    <SectionBorder />
                    <div class="mt-10">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Usuarios en todos los equipos
                        </h3>
                        <p class="text-sm text-gray-500 mb-3">
                            Listado global de usuarios con su equipo y rol asignado.
                        </p>

                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead >
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">#</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Usuario</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Correo</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500">Equipo</th>
                                    </tr>
                                </thead>
                                <tbody class=" divide-y divide-gray-100">
                                    <tr v-for="(row, i) in allUserTeamRows" :key="`${row.user_id}-${row.team_id}`">
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ i + 1 }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ row.user_name }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ row.user_email }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ row.team_description }}</td>
                                    </tr>
                                    <tr v-if="!allUserTeamRows.length">
                                        <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">Sin
                                            registros.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
