<script setup>
import { reactive } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const props = defineProps({
  team: Object,
  members: Array,
  roles: Array,
})

const form = reactive({ email: '', role: '' })

function addMember() {
  router.post(route('teams.members.store', props.team.id), form)
}

function changeRole(userId, role) {
  router.patch(route('teams.members.updateRole', [props.team.id, userId]), { role })
}

function removeMember(userId) {
  router.delete(route('teams.members.destroy', [props.team.id, userId]))
}
</script>

<template>
  <div class="space-y-6">
    <h1 class="text-xl font-bold">Miembros de {{ props.team.name }}</h1>

    <div class="p-4 rounded border">
      <h2 class="font-semibold mb-2">Agregar miembro</h2>
      <div class="flex gap-2">
        <input v-model="form.email" type="email" placeholder="email@dominio.com" class="input" />
        <select v-model="form.role" class="input">
          <option value="">(sin rol)</option>
          <option v-for="r in roles" :key="r.key" :value="r.key">{{ r.name }}</option>
        </select>
        <button @click="addMember" class="btn">Agregar</button>
      </div>
    </div>

    <div class="p-4 rounded border">
      <h2 class="font-semibold mb-2">Miembros</h2>
      <table class="w-full">
        <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th></th></tr></thead>
        <tbody>
          <tr v-for="m in members" :key="m.id">
            <td>{{ m.name }}</td>
            <td>{{ m.email }}</td>
            <td>
              <select :value="m.role" @change="changeRole(m.id, $event.target.value)" class="input">
                <option v-for="r in roles" :key="r.key" :value="r.key">{{ r.name }}</option>
              </select>
            </td>
            <td>
              <button @click="removeMember(m.id)" class="btn btn-danger">Eliminar</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style>
.input{ @apply border rounded px-2 py-1; }
.btn{ @apply bg-black text-white rounded px-3 py-1; }
.btn-danger{ @apply bg-red-600; }
</style>
