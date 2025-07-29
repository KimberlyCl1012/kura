<script setup>
import AppLayout from "../../Layouts/sakai/AppLayout.vue";
import { FilterMatchMode } from "@primevue/core/api";
import { InputText, Dropdown, MultiSelect, Button, Dialog, Toast } from "primevue";
import { useToast } from "primevue/usetoast";
import { ref } from "vue";
import axios from "axios";

const props = defineProps({
    users: Array,
});

const toast = useToast();
const dt = ref();
const userDialog = ref(false);
const isEditMode = ref(false);
const submitted = ref(false);
const isSaving = ref(false);
const user = ref({});
const userList = ref([...props.users]);

const allPermissions = [
    "create",
    "read",
    "update",
    "delete",
    "editor:edit-all",
];

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

function openNew() {
    user.value = {
        id: null,
        denied_permissions: [],
    };
    submitted.value = false;
    isEditMode.value = false;
    userDialog.value = true;
}

function editUser(data) {
    user.value = {
        id: data.id,
        name: data.name,
        email: data.email,
        denied_permissions: data.denied_permissions?.map(p => p.permission) || [],
    };
    submitted.value = false;
    isEditMode.value = true;
    userDialog.value = true;
}

function hideDialog() {
    userDialog.value = false;
    submitted.value = false;
    user.value = {};
}

function saveUser() {
    submitted.value = true;

    if (!user.value.id) {
        toast.add({ severity: "warn", summary: "Usuario requerido", detail: "Selecciona un usuario.", life: 3000 });
        return;
    }

    isSaving.value = true;

    axios.post("/user_denied_permissions", {
        user_id: user.value.id,
        permissions: user.value.denied_permissions,
    })
        .then((response) => {
            toast.add({ severity: "success", summary: "Éxito", detail: response.data.message, life: 3000 });

            // Actualizar localmente denied_permissions
            const index = userList.value.findIndex(u => u.id === user.value.id);
            if (index !== -1) {
                userList.value[index].denied_permissions = user.value.denied_permissions.map(p => ({ permission: p }));
            }

            userDialog.value = false;
        })
        .catch((error) => {
            const msg = error.response?.data?.message || "Error al guardar.";
            toast.add({ severity: "error", summary: "Error", detail: msg, life: 5000 });
        })
        .finally(() => {
            isSaving.value = false;
            user.value = {};
        });
}

function exportCSV() {
    dt.value.exportCSV();
}
</script>

<template>
    <AppLayout title="Permisos (restricciones)">
        <Toast />
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="Nuevo" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
                </template>
                <template #end>
                    <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="exportCSV" />
                </template>
            </Toolbar>

            <DataTable ref="dt" :value="userList" dataKey="id" :paginator="true" :rows="10" :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Ver {first} al {last} de {totalRecords} registros">
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Permisos (restricciones)</h4>
                        <IconField>
                            <InputIcon><i class="pi pi-search" /></InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Buscar..." />
                        </IconField>
                    </div>
                </template>

                <Column header="#" style="min-width: 6rem">
                    <template #body="{ index }">{{ index + 1 }}</template>
                </Column>
                <Column field="name" header="Nombre" style="min-width: 16rem" />
                <Column header="Permisos restringidos" style="min-width: 20rem">
                    <template #body="{ data }">
                        <ul v-if="data.denied_permissions.length">
                            <li v-for="p in data.denied_permissions" :key="p.permission" class="text-red-600 text-sm">•
                                {{ p.permission }}</li>
                        </ul>
                        <span v-else class="text-gray-500 text-sm">Sin restricciones</span>
                    </template>
                </Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="{ data }">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editUser(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="userDialog" :style="{ width: '450px' }" :header="isEditMode ? 'Editar' : 'Crear'"
            :modal="true">
            <div class="flex flex-col gap-6">
                <div>
                    <label class="block font-bold mb-2">Usuario<span class="text-red-600">*</span></label>
                    <Dropdown v-model="user.id" :options="props.users" optionLabel="name" optionValue="id"
                        placeholder="Selecciona un usuario" class="w-full" :disabled="isEditMode" />
                </div>

                <div>
                    <label class="block font-bold mb-2">Permisos a restringir</label>
                    <MultiSelect v-model="user.denied_permissions" :options="allPermissions" class="w-full"
                        placeholder="Selecciona uno o más permisos" display="chip" />
                </div>
            </div>

            <template #footer>
                <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
                <Button :label="isEditMode ? 'Actualizar' : 'Guardar'" icon="pi pi-check" :loading="isSaving"
                    :disabled="isSaving" @click="saveUser" />
            </template>
        </Dialog>
    </AppLayout>
</template>
