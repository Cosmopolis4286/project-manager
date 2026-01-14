<script setup>
/**
 * Cartão de métricas para painel de controle.
 *
 * Props:
 * - value: number | string
 * - label: string
 * - color: string (clases Tailwind para el valor, ej: 'text-blue-600')
 */

defineProps({
    value: {
        type: [Number, String],
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    color: {
        type: String,
        default: "text-gray-800",
    },
});
</script>

<template>
    <div class="bg-white p-3 rounded-lg shadow text-center">
        <div class="text-3xl font-bold" :class="color">
            {{ value }}
        </div>
        <div class="mt-2 font-semibold text-gray-700">
            {{ label }}
        </div>
    </div>
</template>
