<script setup lang="ts">
import Heading from '@/components/Heading.vue';

type Props = {
    title: string;
    description?: string;
};

defineProps<Props>();
</script>

<template>
    <div class="flex flex-1 flex-col gap-6 p-4">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <Heading :title="title" :description="description" />
            <div v-if="$slots.actions" class="flex flex-wrap gap-2">
                <slot name="actions" />
            </div>
        </div>
        <slot />
    </div>
</template>