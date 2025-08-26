<script setup>
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import { useLayout } from "./composables/layout";
import AppConfigurator from "./AppConfigurator.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

const page = usePage();
const userRole = computed(() => page.props.userRole);
const userPermissions = computed(() => page.props.userPermissions);

const { toggleMenu, toggleDarkMode, isDarkTheme } = useLayout();

const switchToTeam = (team) => {
  router.put(
    route("current-team.update"),
    {
      team_id: team.id,
    },
    {
      preserveState: false,
    }
  );
};

const logout = () => {
  router.post(route("logout"));
};

console.log(userRole.value, userPermissions.value)

</script>

<template>
  <div class="layout-topbar">
    <div class="layout-topbar-logo-container">
      <button class="layout-menu-button layout-topbar-action" @click="toggleMenu">
        <i class="pi pi-bars"></i>
      </button>
      <a :href="route('dashboard')"
        class="layout-topbar-logo flex items-center gap-2 font-bold text-xl text-primary-600">
        <img style="width: 40%;" src="../../../img/logos/red/kura_1.svg" alt="Procomsa Logo" />
      </a>
    </div>

    <div class="layout-topbar-actions">
      <!-- Botón de modo oscuro -->
      <div class="layout-config-menu">
        <button type="button" class="layout-topbar-action mt-2" @click="toggleDarkMode">
          <i :class="['pi', { 'pi-moon': isDarkTheme, 'pi-sun': !isDarkTheme }]"></i>
        </button>
        <li class="border-surface lg:border-t-0 list-none">
          <Dropdown align="right" width="64">
            <template #trigger>
              <button type="button" class="layout-topbar-action mt-2">
                <i class="pi pi-list-check"></i>
              </button>
            </template>
            <template #content>
              <div style="width: 195px">
                <div class="block px-4 py-2 text-xs text-gray-400">Catálogos</div>
                <DropdownLink :href="route('users.index')">
                  • Usuarios
                </DropdownLink>
                <DropdownLink :href="route('sites.index')">
                  • Sitios
                </DropdownLink>
                <DropdownLink :href="route('address.index')">
                  • Direcciones
                </DropdownLink>
                <DropdownLink :href="route('body_locations.index')">
                  • Ubicaciones corporales
                </DropdownLink>
                <DropdownLink :href="route('body_sublocations.index')">
                  • Ubicaciones corporales (Secundarias)
                </DropdownLink>
                <DropdownLink :href="route('wound_types.index')">
                  • Tipos de herida
                </DropdownLink>
                <DropdownLink :href="route('wound_subtypes.index')">
                  • Subtipos de herida
                </DropdownLink>
                <DropdownLink :href="route('methods.index')">
                  • Métodos del tratamiento
                </DropdownLink>
                <DropdownLink :href="route('submethods.index')">
                  • Submetodos del tratamiento
                </DropdownLink>
                <DropdownLink :href="route('wound_assessment.index')">
                  • Evaluaciones de la herida
                </DropdownLink>
                <DropdownLink :href="route('wound_phases.index')">
                  • Fases de la herida
                </DropdownLink>
              </div>
            </template>
          </Dropdown>
        </li>
        <li class="border-surface lg:border-t-0 list-none">
          <Dropdown align="right" width="64">
            <template #trigger>
              <button type="button" class="layout-topbar-action mt-2">
                <i class="pi pi-cog"></i>
              </button>
            </template>
            <template #content>
              <div style="width: 150px">
                <div class="block px-4 py-2 text-xs text-gray-400">Configuración</div>
                <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)">
                  • Roles
                </DropdownLink>
                <!-- <DropdownLink :href="route('teams.create')"> • Crear Rol </DropdownLink> -->
                <DropdownLink :href="route('permissions.index')"> • Permisos </DropdownLink>

                <div class="border-t border-gray-200" v-if="$page.props.auth.user.all_teams.length > 1" />

                <div class="block px-4 py-2 text-xs text-gray-400">Cambiar rol</div>
                <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                  <form @submit.prevent="switchToTeam(team)">
                    <DropdownLink as="button">
                      <div class="flex items-center">
                        <svg v-if="team.id == $page.props.auth.user.current_team_id" class="me-2 size-5 text-green-400"
                          xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                          stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>

                        <div>{{ team.name }}</div>
                      </div>
                    </DropdownLink>
                  </form>
                </template>
              </div>
            </template>
          </Dropdown>
        </li>
        <li class="border-t border-surface lg:border-t-0 list-none">
          <Dropdown align="right" width="64">
            <template #trigger>
              <button type="button"
                class="inline-flex items-center px-3 py-2 text-sm leading-4 font-medium rounded-md text-white-500 hover:text-white-700">
                <img class="size-10 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url"
                  :alt="$page.props.auth.user.name" />&nbsp;<b>{{ $page.props.auth.user.name }}</b>
              </button>
            </template>

            <template #content>
              <div style="width: 150px">
                <AppConfigurator />
                <div class="block px-4 py-2 text-xs text-gray-400">Mi perfil</div>
                <DropdownLink :href="route('profile.show')"> • Mi perfil </DropdownLink>
                <form @submit.prevent="logout">
                  <DropdownLink as="button">
                    <i class="pi pi-sign-out" style="font-size: 9px"></i> Salir
                  </DropdownLink>
                </form>
              </div>
            </template>
          </Dropdown>
        </li>
      </div>

      <!-- Botón menú desplegable -->
      <!-- <button
        class="layout-topbar-menu-button layout-topbar-action"
        v-styleclass="{
          selector: '@next',
          enterFromClass: 'hidden',
          enterActiveClass: 'animate-scalein',
          leaveToClass: 'hidden',
          leaveActiveClass: 'animate-fadeout',
          hideOnOutsideClick: true,
        }"
      >
        <i class="pi pi-ellipsis-v"></i>
      </button> -->

      <!-- Menú desplegable principal -->
      <!-- <div class="layout-topbar-menu hidden lg:block">
        <div class="layout-topbar-menu-content">
          <li class="border-t border-surface lg:border-t-0 list-none">
            <Dropdown align="right" width="64">
              <template #trigger>
                <button
                  type="button"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white-500 hover:text-white-700"
                >
                  <img
                    class="size-10 rounded-full object-cover"
                    :src="$page.props.auth.user.profile_photo_url"
                    :alt="$page.props.auth.user.name"
                  />&nbsp;<b>{{ $page.props.auth.user.name }}</b>
                </button>
              </template>

              <template #content>
                <div class="p-1 bg-white text-black dark:bg-surface-900">
                  <AppConfigurator />
                  <DropdownLink
                    :href="route('teams.show', $page.props.auth.user.current_team)"
                  >
                    • Prueba
                  </DropdownLink>
                </div>
              </template>
            </Dropdown>
          </li>
        </div>
      </div> -->
    </div>
  </div>
</template>
