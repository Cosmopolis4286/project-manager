<script setup>
/**
 * Dashboard principal del usuario autenticado.
 *
 * Responsabilidades:
 * - Mostrar métricas (projects, tasks, alerts)
 * - Mostrar notificaciones recientes
 * - Proveer acciones rápidas de navegación
 *
 */

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage, router } from "@inertiajs/vue3";

/**
 * Props recibidas desde Inertia (DashboardController@index)
 *
 * Estructura esperada:
 * {
 *   stats: {
 *     active_projects: number,
 *     pending_tasks: number,
 *     recent_alerts: number
 *   },
 *   notifications: string[]
 * }
 *
 * Se definen valores por defecto para evitar errores
 * durante renders intermedios de Inertia.
 */
const page = usePage();

const stats = page.props.stats ?? {
    active_projects: 0,
    pending_tasks: 0,
    recent_alerts: 0,
};

const notifications = page.props.notifications ?? [];

/**
 * Navega a la creación de un nuevo proyecto
 * Usa ruta nombrada para soportar subdirectorios y APP_URL dinámico
 */
const createNewProject = () => {
    router.visit(route("projects.create"));
};

/**
 * Navega a la lista de tareas filtradas por estado "pending"
 * (asegura compatibilidad futura con query params)
 */
const viewPendingTasks = () => {
    router.visit(route("tasks.index", { status: "pending" }));
};

/**
 * Navega a la vista de alertas recientes
 */
const viewAlerts = () => {
    router.visit(route("alerts.recent"));
};
</script>

<template>
    <!-- Head SOLO de Inertia (no vueuse/head) -->
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Bienvenida -->
            <section class="bg-white shadow-sm rounded-lg p-6">
                <p class="text-lg font-medium">Olá, você está logado!</p>
                <p class="mt-2 text-gray-600">
                    Aqui está um resumo rápido do seu projeto.
                </p>
            </section>

            <!-- Métricas -->
            <section class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-lg shadow text-center">
                    <div class="text-3xl font-bold text-blue-600">
                        {{ stats.active_projects }}
                    </div>
                    <div class="mt-2 font-semibold text-gray-700">
                        Projetos Ativos
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow text-center">
                    <div class="text-3xl font-bold text-green-600">
                        {{ stats.pending_tasks }}
                    </div>
                    <div class="mt-2 font-semibold text-gray-700">
                        Tarefas Pendentes
                    </div>
                </div>

                <div class="bg-white p-6 rounded-lg shadow text-center">
                    <div class="text-3xl font-bold text-red-600">
                        {{ stats.recent_alerts }}
                    </div>
                    <div class="mt-2 font-semibold text-gray-700">
                        Alertas Recentes
                    </div>
                </div>
            </section>

            <!-- Notificaciones -->
            <section class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">
                    Notificações Recentes
                </h3>

                <ul
                    v-if="notifications.length"
                    class="list-disc list-inside space-y-2 text-gray-700"
                >
                    <li
                        v-for="(notification, index) in notifications"
                        :key="index"
                    >
                        {{ notification }}
                    </li>
                </ul>

                <p v-else class="text-sm text-gray-500">
                    Nenhuma notificação recente.
                </p>
            </section>

            <!-- Acciones rápidas -->
            <section class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Ações Rápidas</h3>

                <div class="flex flex-wrap gap-4">
                    <button
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
                        @click="createNewProject"
                    >
                        Criar novo projeto
                    </button>

                    <button
                        class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition"
                        @click="viewPendingTasks"
                    >
                        Ver tarefas pendentes
                    </button>

                    <button
                        class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 transition"
                        @click="viewAlerts"
                    >
                        Ver alertas
                    </button>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>
