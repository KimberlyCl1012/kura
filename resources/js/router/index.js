import { createRouter, createWebHistory } from 'vue-router';
import Dashboard from '@/Pages/Dashboard.vue';

const routes = [
    { path: '/' },
    { path: '/dashboard', component: Dashboard }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
