<script setup>
import AppLayout from "../../Layouts/sakai/AppLayout.vue";
import { FilterMatchMode } from "@primevue/core/api";
import { InputText, Password } from "primevue";
import { useToast } from "primevue/usetoast";
import { ref } from "vue";
import axios from "axios";

const props = defineProps({
    users: {
        type: Array,
        required: true,
        default: () => [],
    },
});

const toast = useToast();
const dt = ref();
const userDialog = ref(false);
const isEditMode = ref(false);
const submitted = ref(false);
const isSaving = ref(false);
const user = ref({});
const userList = ref([...props.users]);
const validationErrors = ref({});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

function openNew() {
    user.value = {};
    submitted.value = false;
    isEditMode.value = false;
    validationErrors.value = {};
    userDialog.value = true;
}

function editUser(data) {
    user.value = { ...data };
    submitted.value = false;
    isEditMode.value = true;
    validationErrors.value = {};
    userDialog.value = true;
}

function hideDialog() {
    userDialog.value = false;
    submitted.value = false;
    validationErrors.value = {};
    user.value = {};
}

function saveUser() {
    submitted.value = true;

    if (!user.value.name?.trim() || !user.value.email?.trim()) return;

    const payload = {
        name: user.value.name,
        email: user.value.email,
        password: user.value.password || null,
    };

    isSaving.value = true;

    if (isEditMode.value && user.value.id) {
        axios
            .put(`/users/${user.value.id}`, payload)
            .then((response) => {
                const res = response.data;
                if (res.success) {
                    const index = userList.value.findIndex((u) => u.id === user.value.id);
                    if (index !== -1) {
                        userList.value[index] = res.data;
                    }
                    toast.add({ severity: "success", summary: "Actualizado", detail: res.message, life: 3000 });
                    userDialog.value = false;
                }
            })
            .catch((error) => {
                const msg = error.response?.data?.message || "Error al actualizar.";
                toast.add({ severity: "error", summary: "Error", detail: msg, life: 5000 });
            })
            .finally(() => {
                isSaving.value = false;
                user.value = {};
            });
    } else {
        axios
            .post("/users", payload)
            .then((response) => {
                const res = response.data;
                if (res.success) {
                    userList.value.push(res.data);
                    toast.add({ severity: "success", summary: "Creado", detail: res.message, life: 3000 });
                    userDialog.value = false;
                }
            })
            .catch((error) => {
                if (error.response?.status === 422 && error.response.data?.errors) {
                    validationErrors.value = error.response.data.errors || {};
                    const first = Object.values(validationErrors.value)[0]?.[0];
                    toast.add({
                        severity: "warn",
                        summary: "Revisa los campos",
                        detail: first || "Hay errores de validación.",
                        life: 5000,
                    });
                } else {
                    const msg = error.response?.data?.message || "Ocurrió un error.";
                    toast.add({ severity: "error", summary: "Error", detail: msg, life: 5000 });
                }
            })
            .finally(() => {
                isSaving.value = false;
            });
    }
}

function exportCSV() {
    dt.value.exportCSV();
}
</script>

<template>
    <AppLayout title="Usuarios">
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
                        <h4 class="m-0">Usuarios</h4>
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
                <Column field="email" header="Correo" style="min-width: 18rem" />
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
                    <label class="block font-bold mb-2">Nombre<span class="text-red-600">*</span></label>
                    <InputText v-model="user.name" required
                        :invalid="(submitted && !user.name) || !!validationErrors.name" class="w-full" />
                    <small v-if="submitted && !user.name" class="text-red-500">El nombre es requerido.</small>
                    <small v-else-if="validationErrors.name" class="text-red-500">{{ validationErrors.name[0] }}</small>

                </div>
                <div>
                    <label class="block font-bold mb-2">Correo<span class="text-red-600">*</span></label>
                    <InputText v-model="user.email" required
                        :invalid="(submitted && !user.email) || !!validationErrors.email" class="w-full" />
                    <small v-if="submitted && !user.email" class="text-red-500">El correo es requerido.</small>
                    <small v-else-if="validationErrors.email" class="text-red-500">{{ validationErrors.email[0]
                    }}</small>

                </div>
                <div>
                    <label class="block font-bold mb-2">Contraseña <span class="text-red-600">*</span><span
                            v-if="isEditMode">(opcional)</span></label>
                    <Password v-model="user.password" toggleMask :feedback="false" class="w-full" inputClass="w-full"
                        :invalid="!!validationErrors.password || (!isEditMode && submitted && !user.password)" />
                    <small v-if="!isEditMode && submitted && !user.password" class="text-red-500">
                        La contraseña es requerida.
                    </small>
                    <small v-else-if="validationErrors.password" class="text-red-500">
                        {{ validationErrors.password[0] }}
                    </small>
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
