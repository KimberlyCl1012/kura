<script setup>
import AppLayout from '../../Layouts/Sakai/AppLayout.vue';
import CreateTeamForm from '@/Pages/Teams/Partials/CreateTeamForm.vue';
</script>

<template>
    <AppLayout title="Crear rol">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Crear rol
            </h2>
        </template>

        <div style="background-color:white">
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <CreateTeamForm />
            </div>
        </div>
    </AppLayout>
</template>
