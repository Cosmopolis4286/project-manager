<script setup>
/**
 * Buscador de projetos.
 *
 * Responsabilidades:
 * - Gerenciar input de busca
 * - Sincronizar termo com query string
 * - Não aplicar filtros localmente (server-side)
 */

import { ref, watch } from "vue";
import { router } from "@inertiajs/vue3";

/**
 * Props
 */
const props = defineProps({
    modelValue: {
        type: String,
        default: "",
    },
});

/**
 * Emits
 */
const emit = defineEmits(["update:modelValue"]);

/**
 * Valor local do input
 */
const search = ref(props.modelValue);

/**
 * Emite mudanças para o pai
 */
watch(search, (value) => {
    emit("update:modelValue", value);
});
</script>

<template>
    <input
        v-model="search"
        type="search"
        placeholder="Buscar por nome ou descrição..."
        class="border rounded px-3 py-2 text-sm w-64 focus:outline-none focus:ring focus:border-blue-400"
        aria-label="Buscar projetos"
    />
</template>
