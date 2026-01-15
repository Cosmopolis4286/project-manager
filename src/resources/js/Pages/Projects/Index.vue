<script setup>
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

defineProps({
    projects: Array,
});

function goToCreate() {
    router.visit(route("projects.create"));
}

function goToShow(id) {
    router.visit(route("projects.show", id));
}

function goToEdit(id) {
    router.visit(route("projects.edit", id));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Projetos
                </h2>
                <button
                    @click.prevent="goToCreate"
                    class="bg-blue-600 text-white px-2 rounded hover:bg-blue-700 transition"
                    type="button"
                >
                    + Novo Projeto
                </button>
            </div>
        </template>

        <section class="p-6 max-w-7xl mx-auto">
            <div
                class="grid gap-6 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="project in projects"
                    :key="project.id"
                    class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 p-6 flex flex-col justify-between"
                >
                    <header>
                        <h3
                            class="text-xl font-semibold mb-2 truncate cursor-pointer"
                            :title="project.name"
                            @click="goToShow(project.id)"
                        >
                            {{ project.name }}
                        </h3>
                        <p class="text-gray-600 text-sm mb-4">
                            {{ project.tasks_count }}
                            {{
                                project.tasks_count === 1 ? "tarefa" : "tarefas"
                            }}
                        </p>
                    </header>

                    <footer class="flex items-center justify-between mt-auto">
                        <span
                            class="inline-block px-3 py-1 rounded-full text-sm font-medium"
                            :class="
                                project.health === 'Em Alerta'
                                    ? 'bg-red-100 text-red-700'
                                    : 'bg-green-100 text-green-700'
                            "
                        >
                            {{ project.health }}
                        </span>
                        <button
                            @click="goToEdit(project.id)"
                            class="text-blue-600 hover:text-blue-800 font-medium text-sm"
                            type="button"
                        >
                            Editar
                        </button>
                    </footer>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
