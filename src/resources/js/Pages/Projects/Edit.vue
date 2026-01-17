<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import { toRefs } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

/**
 * Recebe o projeto a ser editado via props.
 */
const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
});

// Extrai propriedades para evitar warnings de reatividade
const { project } = toRefs(props);

/**
 * Formulário reativo com dados do projeto.
 * Inicializa com valores vindos da prop project.
 */
const form = useForm({
    name: project.value.name,
    description: project.value.description ?? "",
});

/**
 * Submete a atualização do projeto via PUT.
 */
function submit() {
    form.put(route("projects.update", project.value.id));
}
</script>

<template>
    <Head title="Editar Projeto" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar Projeto
            </h2>
        </template>
        <div class="py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="max-w-md mx-auto p-4 bg-white rounded shadow">
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <label for="name" class="block font-medium">Nome</label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="w-full border rounded px-3 py-2"
                            required
                            autocomplete="off"
                        />
                        <span
                            v-if="form.errors.name"
                            class="text-red-600 text-sm"
                            >{{ form.errors.name }}</span
                        >
                    </div>

                    <div>
                        <label for="description" class="block font-medium"
                            >Descrição</label
                        >
                        <textarea
                            id="description"
                            v-model="form.description"
                            class="w-full border rounded px-3 py-2"
                        ></textarea>
                        <span
                            v-if="form.errors.description"
                            class="text-red-600 text-sm"
                            >{{ form.errors.description }}</span
                        >
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50"
                    >
                        Salvar
                    </button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
