<script setup>
import AppLayout from "../../../Layouts/sakai/AppLayout.vue";
import { FilterMatchMode } from "@primevue/core/api";
import { InputText, Select } from "primevue";
import { useToast } from "primevue/usetoast";
import { ref } from "vue";
import axios from "axios";

const props = defineProps({
    addresses: {
        type: Array,
        required: true,
        default: () => [],
    },
    states: Array,
});

const toast = useToast();
const dt = ref();

const addressDialog = ref(false);
const deleteAddressDialog = ref(false);
const isEditMode = ref(false);
const isSaving = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const submitted = ref(false);
const address = ref({});
const addressList = ref([...props.addresses]);

function openNew() {
    address.value = {};
    submitted.value = false;
    isEditMode.value = false;
    addressDialog.value = true;
}

function editAddress(data) {
    address.value = { ...data };
    if (!address.value.state_id && data.state) {
        const match = props.states.find(s => s.name === data.state);
        if (match) {
            address.value.state_id = match.id;
        }
    }
    submitted.value = false;
    isEditMode.value = true;
    addressDialog.value = true;
}

function hideDialog() {
    addressDialog.value = false;
    submitted.value = false;
    address.value = {};
}

function saveAddress() {
    submitted.value = true;

    if (
        address.value.streetAddress?.trim() &&
        address.value.postalCode?.trim() &&
        address.value.state_id
    ) {
        const payload = {
            streetAddress: address.value.streetAddress,
            addressLine2: address.value.addressLine2,
            postalCode: address.value.postalCode,
            city: address.value.city,
            country: address.value.country ?? null,
            state_id: address.value.state_id,
        };

        isSaving.value = true;

        if (isEditMode.value && address.value.address_id) {
            axios
                .put(`/addresses/${address.value.address_id}`, payload)
                .then((response) => {
                    const res = response.data;
                    if (res.success) {
                        toast.add({
                            severity: "success",
                            summary: "Éxito",
                            detail: res.message,
                            life: 3000,
                        });
                        const index = addressList.value.findIndex(
                            (item) => item.address_id === address.value.address_id
                        );
                        if (index !== -1) {
                            addressList.value[index] = res.data;
                        }
                        addressDialog.value = false;
                        address.value = {};
                    } else {
                        toast.add({
                            severity: "error",
                            summary: "Error",
                            detail: res.message,
                            life: 3000,
                        });
                    }
                })
                .catch((error) => {
                    if (error.response?.status === 422) {
                        const validationErrors = error.response.data.errors;
                        const messages = Object.values(validationErrors)
                            .flat()
                            .join("\n");
                        toast.add({
                            severity: "error",
                            summary: "Error de validación",
                            detail: messages,
                            life: 5000,
                        });
                    } else {
                        const msg =
                            error.response?.data?.message ||
                            "Ocurrió un error inesperado.";
                        toast.add({
                            severity: "error",
                            summary: "Error",
                            detail: msg,
                            life: 5000,
                        });
                    }
                    console.error(error);
                })
                .finally(() => {
                    isSaving.value = false;
                });
        } else {
            axios
                .post("/addresses", payload)
                .then((response) => {
                    const res = response.data;
                    if (res.success) {
                        toast.add({
                            severity: "success",
                            summary: "Éxito",
                            detail: res.message,
                            life: 3000,
                        });
                        addressList.value.push(res.data);
                        addressDialog.value = false;
                        address.value = {};
                    } else {
                        toast.add({
                            severity: "error",
                            summary: "Error",
                            detail: res.message,
                            life: 3000,
                        });
                    }
                })
                .catch((error) => {
                    if (error.response?.status === 422) {
                        const validationErrors = error.response.data.errors;
                        const messages = Object.values(validationErrors)
                            .flat()
                            .join("\n");
                        toast.add({
                            severity: "error",
                            summary: "Error de validación",
                            detail: messages,
                            life: 5000,
                        });
                    } else {
                        const msg =
                            error.response?.data?.message ||
                            "Ocurrió un error inesperado.";
                        toast.add({
                            severity: "error",
                            summary: "Error",
                            detail: msg,
                            life: 5000,
                        });
                    }
                    console.error(error);
                })
                .finally(() => {
                    isSaving.value = false;
                });
        }
    }
}

function confirmDeleteAddress(data) {
    address.value = { ...data };
    deleteAddressDialog.value = true;
}

function deleteAddress() {
    if (address.value?.address_id) {
        // Usamos DELETE porque tu controlador es destroy()
        axios
            .delete(`/addresses/${address.value.address_id}`)
            .then((response) => {
                const res = response.data;
                if (res.success) {
                    toast.add({
                        severity: "success",
                        summary: "Éxito",
                        detail: res.message,
                        life: 3000,
                    });

                    // Removemos el elemento eliminado del arreglo para actualizar la tabla
                    addressList.value = addressList.value.filter(
                        (item) => item.address_id !== address.value.address_id
                    );

                    deleteAddressDialog.value = false;
                    address.value = {};
                } else {
                    toast.add({
                        severity: "error",
                        summary: "Error",
                        detail: res.message,
                        life: 3000,
                    });
                }
            })
            .catch((error) => {
                const msg =
                    error.response?.data?.message ||
                    "Ocurrió un error al eliminar.";
                toast.add({
                    severity: "error",
                    summary: "Error",
                    detail: msg,
                    life: 5000,
                });
                console.error(error);
            });
    }
}

function exportCSV() {
    dt.value.exportCSV();
}
</script>

<template>
    <AppLayout title="Direcciones">
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="Nuevo" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
                </template>

                <template #end>
                    <Button label="Exportar" icon="pi pi-upload" severity="secondary" @click="exportCSV" />
                </template>
            </Toolbar>

            <DataTable ref="dt" :value="addressList" dataKey="address_id" :paginator="true" :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Ver {first} al {last} de {totalRecords} registros">
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Direcciones</h4>
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Buscar..." />
                        </IconField>
                    </div>
                </template>

                <Column header="#" style="min-width: 6rem">
                    <template #body="{ index }">
                        {{ index + 1 }}
                    </template>
                </Column>
                <Column field="name" header="Dirección" style="min-width: 16rem">
                    <template #body="{ data }">
                        {{ data.streetAddress }}  {{ data.addressLine2 }} , C.P
                        {{ data.postalCode }}
                    </template>
                </Column>
                <Column field="city" header="Ciudad" style="min-width: 16rem">
                    <template #body="{ data }">
                        {{ data.city }}
                    </template>
                </Column>
                <Column field="state" header="Estado" style="min-width: 16rem">
                    <template #body="{ data }">
                        {{ data.state }}
                    </template>
                </Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="{ data }">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editAddress(data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger"
                            @click="confirmDeleteAddress(data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Dialogo Crear/Editar -->
        <Dialog v-model:visible="addressDialog" :style="{ width: '450px' }"
            :header="isEditMode ? 'Editar registro' : 'Crear registro'" :modal="true">
            <div class="flex flex-col gap-6">
                <div>
                    <label class="block font-bold mb-2">Calle 1<span class="text-red-600">*</span></label>
                    <InputText v-model="address.streetAddress" required :invalid="submitted && !address.streetAddress"
                        class="w-full" />
                    <small v-if="submitted && !address.streetAddress" class="text-red-500">
                        La calle es requerida.
                    </small>
                </div>
                <div>
                    <label class="block font-bold mb-2">Calle 2</label>
                    <InputText v-model="address.addressLine2" class="w-full" />
                </div>
                <div>
                    <label class="block font-bold mb-2">C.P.<span class="text-red-600">*</span></label>
                    <InputText v-model="address.postalCode" required :invalid="submitted && !address.postalCode"
                        class="w-full" />
                    <small v-if="submitted && !address.postalCode" class="text-red-500">
                        El código postal es requerido.
                    </small>
                </div>
                <div>
                    <label for="state_id">Estado<span class="text-red-600">*</span></label>
                    <Select id="state_id" v-model="address.state_id" :options="states" filter optionLabel="name"
                        optionValue="id" class="w-full" :class="{ 'p-invalid': submitted && !address.state_id }"
                        placeholder="Seleccione una opción" />
                    <small v-if="submitted && !address.state_id" class="text-red-500">
                        El estado es requerido.
                    </small>
                </div>
            </div>

            <template #footer>
                <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
                <Button :label="isEditMode ? 'Actualizar' : 'Guardar'" icon="pi pi-check" :disabled="isSaving"
                    :loading="isSaving" @click="saveAddress" />
            </template>
        </Dialog>

        <!-- Confirmación eliminar -->
        <Dialog v-model:visible="deleteAddressDialog" :style="{ width: '450px' }" header="Confirmar" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="address?.streetAddress">
                    ¿Estás seguro que deseas eliminar este registro
                    <b>{{ address.streetAddress }}</b>?
                </span>
                <span v-else>Registro no válido.</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteAddressDialog = false" />
                <Button label="Sí" icon="pi pi-check" text @click="deleteAddress" />
            </template>
        </Dialog>
    </AppLayout>
</template>
