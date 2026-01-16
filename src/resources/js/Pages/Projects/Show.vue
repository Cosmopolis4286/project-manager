<script setup>
import { ref } from "vue";
import { Inertia } from "@inertiajs/inertia";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

defineProps({
    project: Object,
});

function updateStatus(task) {
    Inertia.put(
        route("tasks.update", task.id),
        {
            status: task.status,
        },
        { preserveScroll: true }
    );
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ project.name }}
            </h2>
        </template>
        <div
            v-for="task in project.tasks"
            :key="task.id"
            class="p-4 border rounded mb-2"
        >
            <h3>{{ task.title }}</h3>

            <select v-model="task.status" @change="updateStatus(task)">
                <option value="pending">Pendente</option>
                <option value="in_progress">Em Andamento</option>
                <option value="done">Concluída</option>
            </select>
        </div>
    </AuthenticatedLayout>
</template>
