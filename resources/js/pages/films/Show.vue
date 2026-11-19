<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, Clock, Clapperboard, MapPin } from '@lucide/vue';
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import {
    formatDuration,
    formatSessionDate,
    formatSessionTime,
} from '@/lib/formatDuration';
import { handlePosterError, posterSrc } from '@/lib/posterFallback';
import { create as reservationCreate } from '@/routes/reservations';
import { index as filmsIndex } from '@/routes/films';
import type { Film, FilmSession } from '@/types';

const props = defineProps<{
    film: Film;
    sessions: FilmSession[];
}>();

const sessionsByDate = computed(() => {
    const groups = new Map<string, FilmSession[]>();

    for (const session of props.sessions) {
        const date = session.starts_at.slice(0, 10);
        const list = groups.get(date) ?? [];
        list.push(session);
        groups.set(date, list);
    }

    return [...groups.entries()].map(([date, items]) => ({ date, items }));
});
</script>

<template>
    <Head :title="props.film.title" />

    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 px-4 py-6 md:px-6">
        <Button
            variant="ghost"
            as-child
            class="-ml-2 w-fit self-start text-muted-foreground"
        >
            <Link :href="filmsIndex.url()">
                <ArrowLeft class="mr-2 size-4" />
                Retour à la liste
            </Link>
        </Button>

        <article class="flex flex-col gap-8 lg:flex-row lg:items-start lg:gap-10">
            <div
                class="mx-auto w-full max-w-[280px] shrink-0 sm:max-w-xs lg:mx-0 lg:max-w-sm"
            >
                <Card class="overflow-hidden py-0">
                    <div class="aspect-[2/3] bg-muted">
                        <img
                            :src="posterSrc(props.film.poster)"
                            :alt="`Affiche du film ${props.film.title}`"
                            class="size-full object-cover"
                            width="400"
                            height="600"
                            fetchpriority="high"
                            referrerpolicy="no-referrer"
                            @error="handlePosterError"
                        />
                    </div>
                </Card>
            </div>

            <Card
                class="min-w-0 flex-1 border-sidebar-border/70 shadow-none dark:border-sidebar-border"
            >
                <CardHeader class="space-y-4">
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge variant="secondary" class="gap-1">
                            <Clapperboard class="size-3.5" />
                            {{ props.film.genre }}
                        </Badge>
                        <Badge variant="outline" class="gap-1 font-normal">
                            <Clock class="size-3.5" />
                            {{ formatDuration(props.film.duration) }}
                        </Badge>
                    </div>
                    <CardTitle class="text-2xl sm:text-3xl">
                        {{ props.film.title }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-6 pb-8">
                    <Separator />
                    <section class="space-y-3">
                        <h2 class="text-lg font-semibold tracking-tight">
                            Synopsis
                        </h2>
                        <p
                            class="text-base leading-relaxed text-muted-foreground"
                        >
                            {{ props.film.synopsis }}
                        </p>
                    </section>

                    <Separator />

                    <section class="space-y-5">
                        <div class="space-y-1">
                            <h2 class="text-lg font-semibold tracking-tight">
                                Séances disponibles
                            </h2>
                            <p class="text-sm text-muted-foreground">
                                Les disponibilités affichées sont mises à jour en
                                temps réel. Réservez en ligne puis réglez sur
                                Stripe pour recevoir vos billets avec QR code.
                            </p>
                        </div>

                        <div
                            v-if="sessions.length === 0"
                            class="rounded-lg border border-dashed border-border px-4 py-10 text-center"
                        >
                            <p class="font-medium">
                                Aucune séance programmée
                            </p>
                        </div>

                        <div v-else class="space-y-6">
                            <div
                                v-for="group in sessionsByDate"
                                :key="group.date"
                                class="space-y-3"
                            >
                                <h3
                                    class="flex items-center gap-2 text-base font-semibold"
                                >
                                    <Calendar class="size-4 text-primary" />
                                    {{
                                        formatSessionDate(
                                            group.items[0].starts_at,
                                        )
                                    }}
                                </h3>
                                <ul class="grid gap-3 sm:grid-cols-2">
                                    <li
                                        v-for="session in group.items"
                                        :key="session.id"
                                    >
                                        <Card class="py-4">
                                            <CardContent
                                                class="flex flex-col gap-3 px-4 sm:flex-row sm:items-center sm:justify-between"
                                            >
                                                <div class="min-w-0 space-y-1.5">
                                                    <p
                                                        class="text-sm font-medium tabular-nums"
                                                    >
                                                        {{
                                                            formatSessionTime(
                                                                session.starts_at,
                                                            )
                                                        }}
                                                        <span
                                                            class="text-muted-foreground"
                                                        >
                                                            →
                                                            {{
                                                                formatSessionTime(
                                                                    session.ends_at,
                                                                )
                                                            }}
                                                        </span>
                                                    </p>
                                                    <p
                                                        class="flex items-center gap-1.5 text-sm text-muted-foreground"
                                                    >
                                                        <MapPin
                                                            class="size-3.5 shrink-0"
                                                        />
                                                        {{ session.room_name }}
                                                    </p>
                                                    <p
                                                        class="text-xs text-muted-foreground"
                                                    >
                                                        {{
                                                            session.remaining_seats
                                                        }}
                                                        place(s) disponible(s)
                                                    </p>
                                                </div>
                                                <Button
                                                    v-if="
                                                        session.remaining_seats >
                                                        0
                                                    "
                                                    as-child
                                                    class="w-full shrink-0 sm:w-auto"
                                                >
                                                    <Link
                                                        :href="
                                                            reservationCreate.url(
                                                                session.id,
                                                            )
                                                        "
                                                    >
                                                        Réserver cette séance
                                                    </Link>
                                                </Button>
                                            </CardContent>
                                        </Card>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </section>
                </CardContent>
            </Card>
        </article>
    </div>
</template>