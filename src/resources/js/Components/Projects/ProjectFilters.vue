<script setup>
import ProjectSearch from "@/Components/Projects/ProjectSearch.vue";

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["update:filters"]);
</script>

<template>
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold flex items-center gap-2">
            {{ title }}
        </h3>

        <ProjectSearch
            :model-value="filters.search"
            @update:model-value="
                emit('update:filters', {
                    ...filters,
                    search: $event,
                })
            "
        />
    </div>
</template>
