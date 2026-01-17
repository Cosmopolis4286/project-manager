<script setup>
/**
 * Cartão de projeto com indicador de progresso e estado crítico.
 */

import { computed } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
});

/**
 * Projeto em estado crítico.
 */
const isCritical = computed(() => props.project.health === "Em Alerta");

/**
 * Percentual de tarefas concluídas.
 */
const progress = computed(() => {
    return props.project.progress ?? 0;
});

const goToShow = () => router.visit(route("projects.show", props.project.id));

const goToEdit = () => router.visit(route("projects.edit", props.project.id));
</script>

<template>
    <article
        class="relative bg-white rounded-lg shadow p-6 flex flex-col transition hover:shadow-lg"
        :class="isCritical && 'border-l-4 border-red-500'"
    >
        <span
            v-if="isCritical"
            class="absolute top-3 right-3 text-xs font-semibold text-red-600 bg-red-100 px-2 py-1 rounded"
        >
            CRÍTICO
        </span>

        <header class="mb-4">
            <h3
                class="text-xl font-semibold truncate cursor-pointer hover:text-blue-600"
                @click="goToShow"
            >
                {{ project.name }}
            </h3>

            <p class="text-gray-400 text-sm">
                {{ project.description }}
            </p>

            <p class="text-gray-500 text-sm">
                {{ project.tasks_count }} tarefas
            </p>
        </header>

        <!-- Progresso -->
        <div class="mb-4">
            <div class="flex justify-between text-xs mb-1">
                <span class="text-gray-600">Progresso</span>
                <span class="font-medium">{{ progress }}%</span>
            </div>
            <div class="h-2 bg-gray-200 rounded">
                <div
                    class="h-2 rounded transition-all"
                    :class="progress === 100 ? 'bg-green-600' : 'bg-blue-600'"
                    :style="{ width: progress + '%' }"
                />
            </div>
        </div>

        <footer class="mt-auto flex justify-between items-center">
            <span
                class="px-3 py-1 rounded-full text-sm font-medium"
                :class="
                    isCritical
                        ? 'bg-red-100 text-red-700'
                        : 'bg-green-100 text-green-700'
                "
            >
                {{ project.health }}
            </span>

            <button
                @click="goToEdit"
                class="text-blue-600 hover:text-blue-800 text-sm font-medium"
            >
                Editar
            </button>
        </footer>
    </article>
</template>
