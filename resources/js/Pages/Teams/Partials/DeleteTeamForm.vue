<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    team: Object,
});

const confirmingTeamDeletion = ref(false);
const form = useForm({});

const confirmTeamDeletion = () => {
    confirmingTeamDeletion.value = true;
};

const deleteTeam = () => {
    form.delete(route('teams.destroy', props.team), {
        errorBag: 'deleteTeam',
    });
};
</script>

<template>
    <ActionSection>
        <template #title>
            Eliminar rol
        </template>

        <template #description>
            Eliminar permanentemente este rol
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600">
                Una vez eliminado un equipo, todos sus recursos y datos se borrarán permanentemente. Antes de eliminar este equipo, descargue cualquier dato o información relativa a este equipo que desee conservar.
            </div>

            <div class="mt-5">
                <DangerButton @click="confirmTeamDeletion">
                    Eliminar rol
                </DangerButton>
            </div>

            <!-- Delete Team Confirmation Modal -->
            <ConfirmationModal :show="confirmingTeamDeletion" @close="confirmingTeamDeletion = false">
                <template #title>
                    Eliminar rol
                </template>

                <template #content>
                    ¿Está seguro de que desea eliminar este equipo? Una vez eliminado un equipo, todos sus recursos y datos se borrarán permanentemente.
                </template>

                <template #footer>
                    <SecondaryButton @click="confirmingTeamDeletion = false">
                        Cancelar
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteTeam"
                    >
                        Eliminar rol
                    </DangerButton>
                </template>
            </ConfirmationModal>
        </template>
    </ActionSection>
</template>
