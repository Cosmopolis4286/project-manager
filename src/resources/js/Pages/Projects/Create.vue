<script setup>
import { useForm } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const form = useForm({
    name: "",
    description: "",
});

function submit() {
    form.post(route("projects.store"));
}
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Criar Projeto
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
                        Criar
                    </button>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
