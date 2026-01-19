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
 * Props recebidas do backend contendo os projetos e filtros atuais.
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
 * Estado local da lista de projetos para manipulação visual,
 * especialmente para o drag & drop.
 */
const localProjects = ref([...props.projects]);

/**
 * Estado dos filtros locais sincronizados com a query string.
 */
const statusFilter = ref(props.filters.status ?? "all");
const searchFilter = ref(props.filters.search ?? "");

/**
 * Computed que indica se algum filtro está ativo.
 * Isso bloqueia a reordenação via drag & drop enquanto ativo.
 */
const isFiltered = computed(() => {
    return statusFilter.value !== "all" || searchFilter.value.length > 0;
});

/**
 * Watch para manter a lista local sincronizada quando
 * os projetos vindos do backend forem atualizados.
 */
watch(
    () => props.projects,
    (projects) => {
        localProjects.value = [...projects];
    },
);

/**
 * Watch para atualizar a URL e filtros via query string
 * quando os filtros forem modificados pelo usuário.
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
 * Flag para indicar estado de persistência da ordenação.
 * Usada para mostrar feedback visual para o usuário.
 */
const isPersisting = ref(false);

/**
 * Persiste a nova ordem dos projetos no backend,
 * enviando o payload apenas quando não há filtros ativos.
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
 * Navega para a página de criação de novo projeto.
 */
const goToCreate = () => router.visit(route("projects.create"));

/**
 * Handler para remoção local de projeto após exclusão bem-sucedida.
 *
 * @param {number} deletedProjectId - ID do projeto que foi excluído.
 */
function handleProjectDeleted(deletedProjectId) {
    localProjects.value = localProjects.value.filter(
        (project) => project.id !== deletedProjectId,
    );
}
</script>

<template>
    <Head title="Projetos" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center gap-4 flex-wrap">
                <h2 class="text-xl font-semibold">Projetos</h2>

                <div class="flex gap-3 items-center">
                    <!-- Componente para busca textual de projetos -->
                    <ProjectSearch v-model="searchFilter" />

                    <!-- Select para filtro por status de saúde do projeto -->
                    <select
                        v-model="statusFilter"
                        class="border rounded px-3 py-2 text-sm"
                    >
                        <option value="all">Todos</option>
                        <option value="Em Alerta">Críticos</option>
                        <option value="Saudável">Saudáveis</option>
                    </select>

                    <!-- Botão para criar novo projeto -->
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
            <!-- Aviso sobre impossibilidade de reordenação se filtros estiverem ativos -->
            <div
                v-if="isFiltered && localProjects.length"
                class="mb-4 text-center text-sm text-gray-500"
            >
                Desative os filtros para reordenar projetos
            </div>

            <!-- Feedback visual durante a persistência da ordenação -->
            <div
                v-if="isPersisting"
                class="mb-4 text-center text-blue-600 font-medium"
            >
                Salvando nova ordem...
            </div>

            <!-- Lista de projetos com suporte a drag & drop para reordenação -->
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
                    <ProjectCard
                        :project="element"
                        @projectDeleted="handleProjectDeleted"
                    />
                </template>
            </draggable>

            <!-- Mensagem para estado vazio, adaptando mensagem conforme filtros -->
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
