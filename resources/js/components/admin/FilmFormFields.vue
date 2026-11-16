<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { posterSrc } from '@/lib/posterFallback';
import { cn } from '@/lib/utils';

defineProps<{
    errors: Record<string, string | undefined>;
    genreOptions: string[];
    requirePoster?: boolean;
    currentPoster?: string | null;
    defaults?: {
        title?: string;
        synopsis?: string;
        duration?: number;
        genre?: string;
    };
}>();
</script>

<template>
    <div class="space-y-4">
        <div class="grid gap-2">
            <Label for="title">Titre</Label>
            <Input
                id="title"
                name="title"
                :default-value="defaults?.title"
                required
            />
            <InputError :message="errors.title" />
        </div>

        <div class="grid gap-2">
            <Label for="synopsis">Synopsis</Label>
            <textarea
                id="synopsis"
                name="synopsis"
                rows="5"
                required
                class="border-input bg-background w-full rounded-md border px-3 py-2 text-sm shadow-xs"
            >{{ defaults?.synopsis ?? '' }}</textarea>
            <InputError :message="errors.synopsis" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="grid gap-2">
                <Label for="duration">Durée (minutes)</Label>
                <Input
                    id="duration"
                    name="duration"
                    type="number"
                    min="1"
                    max="600"
                    :default-value="defaults?.duration"
                    required
                />
                <InputError :message="errors.duration" />
            </div>
            <div class="grid gap-2">
                <Label for="genre">Genre</Label>
                <select
                    id="genre"
                    name="genre"
                    required
                    :class="
                        cn(
                            'border-input bg-background h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs',
                        )
                    "
                >
                    <option value="" disabled :selected="!defaults?.genre">
                        Sélectionner un genre
                    </option>
                    <option
                        v-for="option in genreOptions"
                        :key="option"
                        :value="option"
                        :selected="defaults?.genre === option"
                    >
                        {{ option }}
                    </option>
                </select>
                <InputError :message="errors.genre" />
            </div>
        </div>

        <div class="grid gap-2">
            <Label for="poster">
                Affiche
                <span v-if="!requirePoster" class="text-muted-foreground">
                    (laisser vide pour conserver l'affiche actuelle)
                </span>
            </Label>
            <img
                v-if="currentPoster"
                :src="posterSrc(currentPoster)"
                alt=""
                class="mb-2 h-40 w-28 rounded-md border object-cover"
            />
            <Input
                id="poster"
                name="poster"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                :required="requirePoster"
            />
            <InputError :message="errors.poster" />
        </div>
    </div>
</template>