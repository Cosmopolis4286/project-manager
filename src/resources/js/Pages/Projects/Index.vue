<script setup>
/**
 * Página de listagem de projetos.
 *
 * Responsabilidades:
 * - Renderizar projetos vindos do backend (já filtrados)
 * - Persistir filtros via query string (server-side)
 * - Permitir reordenação apenas quando não há filtros ativos
 *
 * Arquitetura:
 * - CQRS light
 * - Cache por usuário
 */

import { ref, computed, watch } from "vue";
import { Head, router } from "@inertiajs/vue3";
import draggable from "vuedraggable";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ProjectCard from "@/Components/Projects/ProjectCard.vue";
import ProjectSearch from "@/Components/Projects/ProjectSearch.vue";

/**
 * Props vindas do backend
 */
const props = defineProps({
    projects: {
        type: Array,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            status: "all",
            search: "",
        }),
    },
});

/**
 * Projetos locais (apenas para drag & drop)
 */
const localProjects = ref([...props.projects]);

/**
 * Filtros
 */
const statusFilter = ref(props.filters.status ?? "all");
const searchFilter = ref(props.filters.search ?? "");

/**
 * Indica se existe qualquer filtro ativo
 */
const isFiltered = computed(() => {
    return statusFilter.value !== "all" || searchFilter.value.length > 0;
});

/**
 * Sincroniza lista local quando backend atualiza
 */
watch(
    () => props.projects,
    (projects) => {
        localProjects.value = [...projects];
    },
);

/**
 * Atualiza URL ao mudar filtros
 */
watch([statusFilter, searchFilter], () => {
    router.get(
        route("projects.index"),
        {
            status:
                statusFilter.value !== "all" ? statusFilter.value : undefined,
            search: searchFilter.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

/**
 * Estado visual de persistência da ordenação
 */
const isPersisting = ref(false);

/**
 * Persiste nova ordem
 */
function persistOrder() {
    if (isFiltered.value) return;

    isPersisting.value = true;

    const payload = localProjects.value.map((project, index) => ({
        id: project.id,
        position: index + 1,
    }));

    router.post(
        route("projects.reorder"),
        { projects: payload },
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => (isPersisting.value = false),
        },
    );
}

/**
 * Navega para criação
 */
const goToCreate = () => router.visit(route("projects.create"));
</script>

<template>
    <Head title="Projetos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center gap-4 flex-wrap">
                <h2 class="text-xl font-semibold">Projetos</h2>

                <div class="flex gap-3 items-center">
                    <!-- Buscador -->
                    <ProjectSearch v-model="searchFilter" />

                    <!-- Filtro de status -->
                    <select
                        v-model="statusFilter"
                        class="border rounded px-3 py-2 text-sm"
                    >
                        <option value="all">Todos</option>
                        <option value="Em Alerta">Críticos</option>
                        <option value="Saudável">Saudáveis</option>
                    </select>

                    <button
                        @click="goToCreate"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
                    >
                        + Novo Projeto
                    </button>
                </div>
            </div>
        </template>

        <section class="p-6 max-w-7xl mx-auto">
            <!-- Aviso -->
            <div
                v-if="isFiltered && localProjects.length"
                class="mb-4 text-center text-sm text-gray-500"
            >
                Desative os filtros para reordenar projetos
            </div>

            <!-- Feedback -->
            <div
                v-if="isPersisting"
                class="mb-4 text-center text-blue-600 font-medium"
            >
                Salvando nova ordem...
            </div>

            <!-- Lista -->
            <draggable
                v-if="localProjects.length"
                v-model="localProjects"
                item-key="id"
                :disabled="isFiltered"
                class="grid gap-6 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3"
                ghost-class="opacity-50"
                drag-class="scale-105"
                @end="persistOrder"
            >
                <template #item="{ element }">
                    <ProjectCard :project="element" />
                </template>
            </draggable>

            <!-- Estado vazio -->
            <div
                v-else
                class="flex flex-col items-center justify-center text-center py-16 bg-white rounded-lg shadow"
            >
                <p class="text-lg font-medium text-gray-700 mb-2">
                    Nenhum projeto encontrado
                </p>

                <p class="text-sm text-gray-500 mb-6">
                    {{
                        isFiltered
                            ? "Nenhum projeto corresponde aos filtros aplicados."
                            : "Você ainda não criou nenhum projeto."
                    }}
                </p>

                <button
                    @click="goToCreate"
                    class="bg-blue-600 text-white px-5 py-2 rounded hover:bg-blue-700 transition"
                >
                    Criar primeiro projeto
                </button>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
