<script setup lang="ts">
import { ref, reactive, watch, toRefs } from "vue";
import { Inertia } from "@inertiajs/inertia";
import { Head } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

/**
 * Composable simple para mostrar notificaciones toast.
 */
function useToast() {
    const toasts = ref<
        { id: number; message: string; type: "success" | "error" }[]
    >([]);
    let counter = 0;

    function addToast(message: string, type: "success" | "error" = "success") {
        const id = ++counter;
        toasts.value.push({ id, message, type });
        setTimeout(() => {
            toasts.value = toasts.value.filter((t) => t.id !== id);
        }, 4000);
    }

    return { toasts, addToast };
}

interface Task {
    id: number;
    title: string;
    status: "pending" | "in_progress" | "done";
    description?: string;
    deadline?: string | null;
}

interface Project {
    id: number;
    name: string;
    description?: string | null;
    tasks: Task[];
}

const props = defineProps<{
    project: Project;
    createdTask?: Task;
}>();

// Criamos uma lista reativa local para tarefas que inicializa com as tarefas das props
const tasks = ref<Task[]>([...props.project.tasks]);

// Cada vez que createdTask cambia (se crea una nueva), la agregamos reactiva a tasks
watch(
    () => props.createdTask,
    (newTask) => {
        if (newTask) {
            const exists = tasks.value.find((task) => task.id === newTask.id);
            if (!exists) {
                tasks.value.push(newTask);
            }
        }
    }
);

const newTaskTitle = ref("");
const newTaskDescription = ref("");
const newTaskStatus = ref<"pending" | "in_progress" | "done">("pending");
const newTaskDeadline = ref("");
const newTaskErrors = reactive<{
    title?: string;
    description?: string;
    status?: string;
    deadline?: string;
}>({});
const submitting = ref(false);

const { toasts, addToast } = useToast();

/**
 * Atualiza o status de uma tarefa via Inertia.
 */
function updateStatus(task: Task) {
    Inertia.put(
        route("tasks.update", task.id),
        { status: task.status },
        { preserveScroll: true, preserveState: true }
    );
}

/**
 * Cria nova tarefa vinculada ao projeto, com loader e desabilitação de botões.
 * Exibe toast na criação ou erro.
 */
// Função para criar tarefa
function createTask() {
    // Limpa erros anteriores
    Object.keys(newTaskErrors).forEach((key) => delete newTaskErrors[key]);

    // Valida título
    if (!newTaskTitle.value.trim()) {
        newTaskErrors.title = "O título da tarefa é obrigatório.";
    }

    // Valida prazo obrigatório
    if (!newTaskDeadline.value) {
        newTaskErrors.deadline = "O prazo da tarefa é obrigatório.";
    }

    // Se tiver algum erro, sai
    if (Object.keys(newTaskErrors).length) {
        return;
    }

    submitting.value = true;

    Inertia.post(
        route("tasks.store", { project: props.project.id }),
        {
            title: newTaskTitle.value,
            description: newTaskDescription.value,
            status: newTaskStatus.value,
            deadline: newTaskDeadline.value,
        },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                const createdTask = page.props.task as Task | undefined;

                if (
                    createdTask &&
                    typeof createdTask.id === "number" &&
                    typeof createdTask.title === "string" &&
                    ["pending", "in_progress", "done"].includes(
                        createdTask.status
                    )
                ) {
                    tasks.value.push(createdTask);
                }

                // Reseta o form
                newTaskTitle.value = "";
                newTaskDescription.value = "";
                newTaskStatus.value = "pending";
                newTaskDeadline.value = "";
                Object.keys(newTaskErrors).forEach(
                    (key) => delete newTaskErrors[key]
                );
            },
            onError: (errors) => {
                Object.assign(newTaskErrors, errors);
            },
            onFinish: () => {
                submitting.value = false;
            },
        }
    );
}

function formatStatus(status: Task["status"]): string {
    switch (status) {
        case "pending":
            return "Pendente";
        case "in_progress":
            return "Em Andamento";
        case "done":
            return "Concluída";
        default:
            return status;
    }
}
</script>

<template>
    <Head :title="props.project.name" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 truncate"
            >
                {{ props.project.name }}
            </h2>
            <p class="text-gray-600 truncate">
                {{ props.project.description ?? "Sem descrição" }}
            </p>
        </template>

        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <section
                class="bg-white rounded-lg shadow p-8 max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-8"
            >
                <!-- Lista de Tarefas -->
                <div>
                    <h3 class="text-lg font-semibold mb-6 border-b pb-2">
                        Tarefas
                    </h3>

                    <div
                        v-if="tasks.length"
                        class="space-y-5 max-h-[70vh] overflow-auto pr-2"
                    >
                        <div
                            v-for="task in tasks"
                            :key="task.id"
                            class="p-4 border rounded flex flex-col md:flex-row md:items-center md:justify-between gap-3"
                        >
                            <div class="min-w-0">
                                <h4 class="font-medium truncate">
                                    {{ task.title }}
                                </h4>
                                <small class="text-gray-500 block truncate">
                                    Status atual:
                                    <strong>{{
                                        formatStatus(task.status)
                                    }}</strong>
                                </small>
                                <small
                                    v-if="task.deadline"
                                    class="text-xs text-gray-400"
                                >
                                    Prazo:
                                    {{
                                        new Date(task.deadline).toLocaleString()
                                    }}
                                </small>
                            </div>

                            <select
                                v-model="task.status"
                                @change="updateStatus(task)"
                                class="border rounded px-3 py-1 w-40"
                                aria-label="Atualizar status da tarefa"
                                :disabled="submitting"
                            >
                                <option value="pending">Pendente</option>
                                <option value="in_progress">
                                    Em Andamento
                                </option>
                                <option value="done">Concluída</option>
                            </select>
                        </div>
                    </div>

                    <p v-else class="text-gray-500">
                        Nenhuma tarefa cadastrada para este projeto.
                    </p>
                </div>

                <!-- Formulário de nova tarefa -->
                <form
                    @submit.prevent="createTask"
                    class="flex flex-col space-y-4"
                >
                    <h3 class="text-lg font-semibold border-b pb-2 mb-4">
                        Adicionar nova tarefa
                    </h3>

                    <div>
                        <label for="new-task" class="block font-medium mb-1"
                            >Título</label
                        >
                        <input
                            id="new-task"
                            v-model="newTaskTitle"
                            type="text"
                            class="w-full border rounded px-3 py-2"
                            placeholder="Título da tarefa"
                            autocomplete="off"
                            aria-describedby="title-error"
                            :disabled="submitting"
                        />
                        <p
                            v-if="newTaskErrors.title"
                            id="title-error"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ newTaskErrors.title }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="new-description"
                            class="block font-medium mb-1"
                            >Descrição (opcional)</label
                        >
                        <textarea
                            id="new-description"
                            v-model="newTaskDescription"
                            class="w-full border rounded px-3 py-2 resize-none"
                            rows="3"
                            placeholder="Descrição da tarefa"
                            aria-describedby="description-error"
                            :disabled="submitting"
                        ></textarea>
                        <p
                            v-if="newTaskErrors.description"
                            id="description-error"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ newTaskErrors.description }}
                        </p>
                    </div>

                    <div
                        class="flex flex-col md:flex-row gap-4 md:items-center"
                    >
                        <div class="flex-1">
                            <label
                                for="new-deadline"
                                class="block font-medium mb-1"
                                >Prazo</label
                            >
                            <input
                                id="new-deadline"
                                v-model="newTaskDeadline"
                                type="datetime-local"
                                class="w-full border rounded px-3 py-2"
                                aria-describedby="deadline-error"
                                :disabled="submitting"
                            />
                            <p
                                v-if="newTaskErrors.deadline"
                                id="deadline-error"
                                class="text-red-600 text-sm mt-1"
                            >
                                {{ newTaskErrors.deadline }}
                            </p>
                        </div>

                        <div class="flex-1">
                            <label
                                for="new-status"
                                class="block font-medium mb-1"
                                >Status</label
                            >
                            <select
                                id="new-status"
                                v-model="newTaskStatus"
                                class="w-full border rounded px-3 py-2"
                                aria-describedby="status-error"
                                :disabled="submitting"
                            >
                                <option value="pending">Pendente</option>
                                <option value="in_progress">
                                    Em Andamento
                                </option>
                                <option value="done">Concluída</option>
                            </select>
                            <p
                                v-if="newTaskErrors.status"
                                id="status-error"
                                class="text-red-600 text-sm mt-1"
                            >
                                {{ newTaskErrors.status }}
                            </p>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition relative flex justify-center items-center"
                        :disabled="submitting"
                    >
                        <svg
                            v-if="submitting"
                            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                        >
                            <circle
                                class="opacity-25"
                                cx="12"
                                cy="12"
                                r="10"
                                stroke="currentColor"
                                stroke-width="4"
                            ></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                            ></path>
                        </svg>
                        {{ submitting ? "Enviando..." : "Adicionar Tarefa" }}
                    </button>
                </form>
            </section>
        </div>

        <!-- Toast notifications -->
        <div
            aria-live="assertive"
            class="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 sm:items-start"
            style="z-index: 9999"
        >
            <div
                class="w-full flex flex-col items-center space-y-4 sm:items-end"
            >
                <transition-group name="toast" tag="div">
                    <div
                        v-for="toast in toasts"
                        :key="toast.id"
                        :class="[
                            'max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto flex ring-1 ring-black ring-opacity-5 overflow-hidden',
                            toast.type === 'success'
                                ? 'border-green-500'
                                : 'border-red-500',
                        ]"
                    >
                        <div class="flex-1 w-0 p-4">
                            <p
                                class="text-sm font-medium text-gray-900"
                                :class="
                                    toast.type === 'success'
                                        ? 'text-green-600'
                                        : 'text-red-600'
                                "
                            >
                                {{ toast.message }}
                            </p>
                        </div>
                    </div>
                </transition-group>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
section > div:first-child > div {
    scrollbar-width: thin;
    scrollbar-color: #9ca3af transparent;
}
section > div:first-child > div::-webkit-scrollbar {
    width: 6px;
}
section > div:first-child > div::-webkit-scrollbar-thumb {
    background-color: #9ca3af;
    border-radius: 3px;
}

/* Animación para toasts */
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}
.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>
