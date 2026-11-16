<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
    errors: Record<string, string | undefined>;
    defaults?: {
        name?: string;
        capacity?: number;
    };
}>();
</script>

<template>
    <div class="space-y-4">
        <div class="grid gap-2">
            <Label for="name">Nom de la salle</Label>
            <Input
                id="name"
                name="name"
                :default-value="defaults?.name"
                required
            />
            <InputError :message="errors.name" />
        </div>

        <div class="grid gap-2">
            <Label for="capacity">Capacité (places)</Label>
            <Input
                id="capacity"
                name="capacity"
                type="number"
                min="1"
                max="1000"
                :default-value="defaults?.capacity"
                required
            />
            <InputError :message="errors.capacity" />
        </div>
    </div>
</template>