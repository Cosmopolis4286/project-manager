<script setup>
import { ref, computed, watch } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import draggable from "vuedraggable";
import ProjectSearch from "@/Components/Projects/ProjectSearch.vue";

const props = defineProps({
    tasks: {
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

const statuses = {
    pending: "Pendente",
    in_progress: "Em Andamento",
    done: "Concluída",
};

const columns = Object.entries(statuses).map(([status, label]) => ({
    status,
    label,
}));

const tasks = ref([...props.tasks]);

const statusFilter = ref(props.filters.status ?? "all");
const searchFilter = ref(props.filters.search ?? "");

watch(
    () => props.tasks,
    (newTasks) => {
        tasks.value = [...newTasks];
    },
);

watch([statusFilter, searchFilter], () => {
    Inertia.get(
        route("tasks.index"),
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

function tasksByStatus(status) {
    return tasks.value.filter((t) => t.status === status);
}

function handleDragEnd(evt, newStatus) {
    const task = evt.item.__vueParentComponent.props.element; // pegar tarefa arrastada

    if (task.status === newStatus) return;

    // Atualiza localmente
    tasks.value = tasks.value.map((t) =>
        t.id === task.id ? { ...t, status: newStatus } : t,
    );

    // Atualiza no backend
    Inertia.put(
        route("tasks.update", task.id),
        { status: newStatus },
        {
            preserveState: true,
            preserveScroll: true,
            onError: () => {
                // Reverte se erro
                tasks.value = [...props.tasks];
            },
        },
    );
}

const goToCreate = () => Inertia.visit(route("tasks.create"));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Kanban de Tarefas" />

        <template #header>
            <div class="flex justify-between items-center gap-4 flex-wrap">
                <h2 class="text-xl font-semibold">Kanban de Tarefas</h2>

                <div class="flex gap-3 items-center">
                    <ProjectSearch v-model="searchFilter" />

                    <select
                        v-model="statusFilter"
                        class="border rounded px-3 py-2 text-sm"
                    >
                        <option value="all">Todos</option>
                        <option value="pending">Pendente</option>
                        <option value="in_progress">Em Andamento</option>
                        <option value="done">Concluída</option>
                    </select>

                    <button
                        @click="goToCreate"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition"
                    >
                        + Nova Tarefa
                    </button>
                </div>
            </div>
        </template>

        <div class="p-6 max-w-7xl mx-auto flex gap-6 overflow-x-auto">
            <div
                v-for="column in columns"
                :key="column.status"
                class="bg-white rounded-lg shadow p-4 w-80 flex-shrink-0 flex flex-col"
            >
                <h2
                    class="font-semibold mb-4 text-center border-b border-gray-300 pb-2"
                >
                    {{ column.label }}
                </h2>

                <draggable
                    :list="tasks"
                    group="tasks"
                    item-key="id"
                    @end="(evt) => handleDragEnd(evt, column.status)"
                    class="flex-1 space-y-3 min-h-[300px] overflow-auto"
                >
                    <template #item="{ element: task }">
                        <div
                            v-if="task.status === column.status"
                            class="bg-gray-50 p-3 rounded shadow cursor-move hover:bg-gray-100"
                        >
                            <h3 class="font-medium">{{ task.title }}</h3>
                            <p class="text-sm text-gray-600 truncate">
                                {{ task.description }}
                            </p>
                            <p class="text-xs mt-2 text-gray-400">
                                Prazo:
                                {{
                                    new Date(task.deadline).toLocaleDateString()
                                }}
                            </p>
                        </div>
                    </template>
                </draggable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
[draggable="true"]:active {
    cursor: grabbing;
}
</style>
