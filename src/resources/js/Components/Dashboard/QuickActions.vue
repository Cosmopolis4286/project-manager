<script setup>
/**
 * Painel de Ações Rápidas do Dashboard.
 * Props:
 * - actions: Array<{
 *     label: string,
 *     onClick: Function,
 *     color?: string
 *   }>
 */

defineProps({
    actions: {
        type: Array,
        required: true,
        default: () => [],
    },
});
</script>

<template>
    <section class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Ações Rápidas</h3>

        <div class="flex flex-wrap gap-4">
            <button
                v-for="(action, index) in actions"
                :key="index"
                class="text-white px-4 py-2 rounded transition"
                :class="action.color ?? 'bg-gray-600 hover:bg-gray-700'"
                @click="action.onClick"
            >
                {{ action.label }}
            </button>
        </div>
    </section>
</template>
