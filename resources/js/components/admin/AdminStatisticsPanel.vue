<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatMoney } from '@/lib/formatMoney';
import { handlePosterError, posterSrc } from '@/lib/posterFallback';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes';

type AttendancePoint = {
    date: string;
    reservations: number;
    places: number;
};

const props = defineProps<{
    period: string;
    periodLabel: string;
    periodOptions: { value: string; label: string }[];
    summary: {
        reservations_total: number;
        reservations_confirmed: number;
        reservations_cancelled: number;
        places_confirmed: number;
        films_scheduled: number;
        sessions_scheduled: number;
        amount_confirmed: number;
    };
    attendance: {
        points: AttendancePoint[];
        totals: { reservations: number; places: number };
    };
    occupancy: {
        global: {
            rate: number;
            reserved_places: number;
            total_capacity: number;
        };
        rooms: {
            room_id: number;
            room_name: string;
            rate: number;
            reserved_places: number;
            total_capacity: number;
        }[];
    };
    popularFilms: {
        id: number;
        title: string;
        poster: string | null;
        reservations_count: number;
        places_sum: number;
    }[];
}>();

const maxPlaces = computed(() =>
    Math.max(1, ...props.attendance.points.map((point) => point.places)),
);

function changePeriod(value: string) {
    router.get(
        dashboard.url({ query: { period: value } }),
        {},
        { preserveScroll: true, replace: true },
    );
}

function formatShortDate(date: string): string {
    return new Date(date + 'T12:00:00').toLocaleDateString('fr-CA', {
        day: 'numeric',
        month: 'short',
    });
}
</script>

<template>
    <section class="space-y-6">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-lg font-semibold">Statistiques</h2>
                <p class="text-sm text-muted-foreground">
                    {{ periodLabel }}
                </p>
            </div>
            <select
                :value="period"
                :class="
                    cn(
                        'h-9 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs',
                    )
                "
                @change="
                    changePeriod(($event.target as HTMLSelectElement).value)
                "
            >
                <option
                    v-for="option in periodOptions"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium">
                        Réservations
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-semibold">
                        {{ summary.reservations_total }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        {{ summary.reservations_confirmed }} confirmées ·
                        {{ summary.reservations_cancelled }} annulées
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium">
                        Places confirmées
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-semibold">
                        {{ summary.places_confirmed }}
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium">Programme</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm text-muted-foreground">
                        {{ summary.films_scheduled }} films ·
                        {{ summary.sessions_scheduled }} séances
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium">
                        Montants confirmés
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-semibold">
                        {{ formatMoney(summary.amount_confirmed) }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Paiements Stripe confirmés sur la période.
                    </p>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">
                        Fréquentation (séances confirmées)
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        {{ attendance.totals.reservations }} réservations ·
                        {{ attendance.totals.places }} places
                    </p>
                    <div
                        v-if="attendance.points.some((p) => p.places > 0)"
                        class="flex h-48 items-end gap-1 overflow-x-auto pb-2"
                    >
                        <div
                            v-for="point in attendance.points"
                            :key="point.date"
                            class="flex min-w-8 flex-1 flex-col items-center gap-1"
                        >
                            <div
                                class="min-h-1 w-full max-w-10 rounded-t bg-primary"
                                :style="{
                                    height: `${(point.places / maxPlaces) * 100}%`,
                                }"
                                :title="`${point.places} place(s)`"
                            />
                            <span
                                class="text-[10px] leading-tight text-muted-foreground"
                            >
                                {{ formatShortDate(point.date) }}
                            </span>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">
                        Aucune fréquentation sur cette période.
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-base">
                        Taux d'occupation (séances passées)
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div>
                        <p class="text-3xl font-semibold">
                            {{ occupancy.global.rate }} %
                        </p>
                        <p class="text-sm text-muted-foreground">
                            {{ occupancy.global.reserved_places }} /
                            {{ occupancy.global.total_capacity }} places
                        </p>
                    </div>
                    <ul
                        v-if="occupancy.rooms.length > 0"
                        class="space-y-2 text-sm"
                    >
                        <li
                            v-for="room in occupancy.rooms"
                            :key="room.room_id"
                            class="flex items-center justify-between gap-4 border-t pt-2"
                        >
                            <span>{{ room.room_name }}</span>
                            <span class="text-muted-foreground">
                                {{ room.rate }} % ({{ room.reserved_places }} /
                                {{ room.total_capacity }})
                            </span>
                        </li>
                    </ul>
                    <p v-else class="text-sm text-muted-foreground">
                        Aucune séance passée sur cette période.
                    </p>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle class="text-base">Films les plus réservés</CardTitle>
            </CardHeader>
            <CardContent>
                <div
                    v-if="popularFilms.length > 0"
                    class="overflow-x-auto rounded-md border"
                >
                    <table class="w-full min-w-[560px] text-sm">
                        <thead class="bg-muted/50 text-left">
                            <tr>
                                <th class="px-4 py-3 font-medium">Affiche</th>
                                <th class="px-4 py-3 font-medium">Film</th>
                                <th class="px-4 py-3 font-medium">
                                    Réservations
                                </th>
                                <th class="px-4 py-3 font-medium">Places</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="film in popularFilms"
                                :key="film.id"
                                class="border-t"
                            >
                                <td class="px-4 py-3">
                                    <img
                                        :src="posterSrc(film.poster)"
                                        :alt="film.title"
                                        class="h-14 w-10 rounded object-cover"
                                        @error="handlePosterError"
                                    />
                                </td>
                                <td class="px-4 py-3 font-medium">
                                    {{ film.title }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ film.reservations_count }}
                                </td>
                                <td class="px-4 py-3">{{ film.places_sum }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm text-muted-foreground">
                    Aucune réservation confirmée sur cette période.
                </p>
            </CardContent>
        </Card>
    </section>
</template>
