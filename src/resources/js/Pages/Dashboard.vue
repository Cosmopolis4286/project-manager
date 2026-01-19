<script setup>
/**
 * Dashboard principal do usuário autenticado.
 *
 * Responsabilidades:
 * - Exibir métricas principais
 * - Mostrar notificações recentes
 * - Listar projetos com indicador de saúde
 * - Prover ações rápidas de navegação
 */

import { Head, usePage, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";

import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import StatsCard from "@/Components/Dashboard/StatsCard.vue";
import QuickActions from "@/Components/Dashboard/QuickActions.vue";
import ProjectFilters from "@/Components/Projects/ProjectFilters.vue";
import ProjectHealthList from "@/Components/Projects/ProjectHealthList.vue";
import NotificationsPanel from "@/Components/Dashboard/NotificationsPanel.vue";

/**
 * Props recebidas via Inertia.
 */
const page = usePage();

/**
 * Objeto reativo que armazena os filtros inicializados a partir da URL.
 * @type {import('vue').Ref<{ search: string }>}
 */
const filters = ref({
    search: page.props.filters?.search ?? "",
});

/**
 * Observa mudanças no objeto de filtros e sincroniza com o servidor
 * enviando uma requisição GET para a rota "dashboard" com os filtros atualizados.
 *
 * @param {Object} value - O valor atual dos filtros observados.
 * @returns {void}
 */
watch(
    filters,
    (value) => {
        router.get(route("dashboard"), value, {
            preserveScroll: true,
            replace: true,
        });
    },
    { deep: true },
);

/**
 * Métricas principais.
 */
const stats = page.props.stats ?? {
    active_projects: 0,
    pending_tasks: 0,
    recent_alerts: 0,
};

/**
 * Notificações recentes.
 */
const notifications = page.props.notifications ?? [];

/**
 * Projetos com indicador de saúde.
 * Sempre existe (array vazio ou com dados).
 */
const projects = page.props.projects ?? [];

/**
 * Navega para criação de um novo projeto.
 */
const createNewProject = () => router.visit(route("projects.create"));

/**
 * Navega para tarefas pendentes.
 */
const viewPendingTasks = () =>
    router.visit(route("tasks.index", { status: "pending" }));

/**
 * Navega para alertas recentes.
 */
const viewAlerts = () => router.visit(route("alerts.recent"));

/**
 * Ações rápidas do dashboard.
 */
const quickActions = [
    {
        label: "Criar novo projeto",
        color: "bg-blue-600 hover:bg-blue-700",
        onClick: createNewProject,
    },
    {
        label: "Ver tarefas pendentes",
        color: "bg-green-600 hover:bg-green-700",
        onClick: viewPendingTasks,
    },
    {
        label: "Ver alertas",
        color: "bg-red-600 hover:bg-red-700",
        onClick: viewAlerts,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Métricas -->
            <section class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <StatsCard
                    :value="stats.active_projects"
                    label="Projetos Ativos"
                    color="text-blue-600"
                />
                <StatsCard
                    :value="stats.pending_tasks"
                    label="Tarefas Pendentes"
                    color="text-green-600"
                />
                <StatsCard
                    :value="stats.recent_alerts"
                    label="Alertas Recentes"
                    color="text-red-600"
                />
            </section>

            <!-- Ações rápidas -->
            <section class="bg-white p-6 rounded-lg shadow">
                <QuickActions :actions="quickActions" />
            </section>

            <!-- Projetos -->
            <section class="bg-white p-6 rounded-lg shadow">
                <ProjectFilters
                    title="📊 Saúde dos Projetos"
                    :filters="filters"
                    @update:filters="filters = $event"
                />

                <ProjectHealthList :projects="projects" :limit="5" />
            </section>

            <!-- Notificações -->
            <section class="bg-white p-6 rounded-lg shadow">
                <NotificationsPanel :notifications="notifications" />
            </section>
        </div>
    </AuthenticatedLayout>
</template>
