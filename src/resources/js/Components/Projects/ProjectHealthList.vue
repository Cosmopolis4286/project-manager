<script setup>
/**
 * Lista de projetos com indicador de saúde e progresso.
 *
 * Funcionalidades:
 * - Skeleton loader
 * - Empty state amigável
 * - Tooltip para projetos em alerta
 * - Barra de progresso (% tarefas concluídas)
 * - Navegação clicável
 */

import { computed } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    projects: {
        type: Array,
        default: () => [],
    },
    limit: {
        type: Number,
        default: 5,
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

/**
 * Limita a quantidade de projetos exibidos.
 */
const visibleProjects = computed(() => props.projects.slice(0, props.limit));

/**
 * Navega para o projeto.
 */
const goToProject = (id) => {
    router.visit(route("projects.show", id));
};
</script>

<template>
    <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
        📊 Saúde dos Projetos
    </h3>

    <!-- Skeleton -->
    <ul v-if="loading" class="space-y-4">
        <li
            v-for="n in limit"
            :key="n"
            class="h-14 bg-gray-200 rounded animate-pulse"
        />
    </ul>

    <!-- Lista -->
    <ul v-else-if="visibleProjects.length" class="space-y-4">
        <li
            v-for="project in visibleProjects"
            :key="project.id"
            @click="goToProject(project.id)"
            class="cursor-pointer hover:bg-gray-50 p-3 rounded transition"
        >
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span
                        :class="
                            project.health === 'Em Alerta'
                                ? 'text-red-600'
                                : 'text-green-600'
                        "
                    >
                        {{ project.health === "Em Alerta" ? "⚠️" : "✅" }}
                    </span>

                    <span class="font-medium text-gray-800">
                        {{ project.name }}
                    </span>
                </div>

                <span
                    class="text-xs font-semibold px-2 py-1 rounded-full relative group"
                    :class="
                        project.health === 'Em Alerta'
                            ? 'bg-red-100 text-red-700'
                            : 'bg-green-100 text-green-700'
                    "
                >
                    {{ project.health }}

                    <!-- Tooltip -->
                    <span
                        v-if="project.health === 'Em Alerta'"
                        class="absolute hidden group-hover:block w-56 text-xs text-white bg-gray-900 rounded p-2 -top-10 right-0 shadow"
                    >
                        Projeto com muitas tarefas atrasadas.
                    </span>
                </span>
            </div>

            <p class="font-medium text-gray-400 mb-1">
                {{ project.description }}
            </p>
            <!-- Barra de progresso -->
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                    class="h-2 rounded-full transition-all"
                    :class="
                        project.progress < 40
                            ? 'bg-red-500'
                            : project.progress < 70
                            ? 'bg-yellow-500'
                            : 'bg-green-600'
                    "
                    :style="{ width: project.progress + '%' }"
                />
            </div>

            <p class="text-xs text-gray-500 mt-1">
                {{ project.tasks_count }} tarefas | {{ project.progress }}%
                concluído
            </p>
        </li>
    </ul>

    <!-- Empty State -->
    <div
        v-else
        class="flex flex-col items-center justify-center text-center py-12 gap-2"
    >
        <span class="text-3xl">📁</span>

        <p class="text-sm font-medium text-gray-600">
            Nenhum projeto disponível
        </p>

        <p class="text-xs text-gray-400 max-w-xs">
            Quando você criar projetos, o status e o progresso aparecerão aqui.
        </p>

        <button
            @click="router.visit(route('projects.index'))"
            class="mt-2 text-xs font-medium text-indigo-600 hover:underline"
        >
            Criar ou visualizar projetos
        </button>
    </div>
</template>
