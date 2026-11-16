<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type OptionFilm = { id: number; title: string; duration: number };
type OptionRoom = { id: number; name: string };

defineProps<{
    errors: Record<string, string | undefined>;
    films: OptionFilm[];
    rooms: OptionRoom[];
    defaults?: {
        film_id?: number;
        room_id?: number;
        session_date?: string;
        start_time?: string;
        end_time?: string;
    };
}>();
</script>

<template>
    <div class="space-y-4">
        <div class="grid gap-2">
            <Label for="film_id">Film</Label>
            <select
                id="film_id"
                name="film_id"
                required
                :class="
                    cn(
                        'border-input bg-background h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs',
                    )
                "
            >
                <option value="" disabled>Sélectionner un film</option>
                <option
                    v-for="film in films"
                    :key="film.id"
                    :value="film.id"
                    :selected="defaults?.film_id === film.id"
                >
                    {{ film.title }} ({{ film.duration }} min)
                </option>
            </select>
            <InputError :message="errors.film_id" />
        </div>

        <div class="grid gap-2">
            <Label for="room_id">Salle</Label>
            <select
                id="room_id"
                name="room_id"
                required
                :class="
                    cn(
                        'border-input bg-background h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs',
                    )
                "
            >
                <option value="" disabled>Sélectionner une salle</option>
                <option
                    v-for="room in rooms"
                    :key="room.id"
                    :value="room.id"
                    :selected="defaults?.room_id === room.id"
                >
                    {{ room.name }}
                </option>
            </select>
            <InputError :message="errors.room_id" />
        </div>

        <div class="grid gap-2">
            <Label for="session_date">Date</Label>
            <Input
                id="session_date"
                name="session_date"
                type="date"
                :default-value="defaults?.session_date"
                required
            />
            <InputError :message="errors.session_date" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="grid gap-2">
                <Label for="start_time">Heure de début</Label>
                <Input
                    id="start_time"
                    name="start_time"
                    type="time"
                    :default-value="defaults?.start_time"
                    required
                />
                <InputError :message="errors.start_time" />
            </div>
            <div class="grid gap-2">
                <Label for="end_time">Heure de fin</Label>
                <Input
                    id="end_time"
                    name="end_time"
                    type="time"
                    :default-value="defaults?.end_time"
                    required
                />
                <InputError :message="errors.end_time" />
            </div>
        </div>
        <p class="text-muted-foreground text-sm">
            L'heure de fin doit correspondre à la durée du film choisi.
        </p>
    </div>
</template>