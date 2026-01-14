<script setup>
/**
 * Panel de Notificações Recentes.
 *
 * Props:
 * - notifications: string[]
 */

defineProps({
    notifications: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <h3 class="text-lg font-semibold mb-4">Notificações Recentes</h3>

    <ul
        v-if="notifications.length"
        class="list-disc list-inside space-y-2 text-gray-700"
    >
        <li v-for="(notification, index) in notifications" :key="index">
            {{ notification }}
        </li>
    </ul>

    <p v-else class="text-sm text-gray-500">Nenhuma notificação recente.</p>
</template>
