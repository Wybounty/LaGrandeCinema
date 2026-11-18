<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { formatDuration } from '@/lib/formatDuration';
import { handlePosterError, posterSrc } from '@/lib/posterFallback';
import { index as filmsIndex, show as filmShow } from '@/routes/films';
import type { Film } from '@/types';

defineProps<{
    films: Film[];
}>();
</script>

<template>
    <Head title="Accueil" />

    <div class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-8 px-4 py-6 md:px-6">
        <Heading
            title="Bienvenue au Grand Cinéma"
            description="Réservez vos places pour les prochaines séances à l'affiche."
        />

        <div
            v-if="films.length === 0"
            class="flex flex-col items-center justify-center rounded-xl border border-dashed border-sidebar-border/70 px-6 py-16 text-center dark:border-sidebar-border"
        >
            <p class="text-xl font-semibold tracking-tight">
                Aucun film à l'affiche
            </p>
            <p class="mt-3 max-w-md text-sm text-muted-foreground">
                Aucune séance future n'est programmée pour le moment. Revenez
                bientôt pour découvrir la prochaine programmation.
            </p>
        </div>

        <template v-else>
            <ul
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <li v-for="film in films" :key="film.id">
                    <Card
                        class="flex h-full flex-col overflow-hidden py-0 transition-shadow hover:shadow-md"
                    >
                        <div class="aspect-[2/3] overflow-hidden bg-muted">
                            <img
                                :src="posterSrc(film.poster)"
                                :alt="`Affiche du film ${film.title}`"
                                class="size-full object-cover"
                                loading="lazy"
                                decoding="async"
                                referrerpolicy="no-referrer"
                                @error="handlePosterError"
                            />
                        </div>
                        <CardHeader class="gap-2 px-4 pb-2">
                            <div class="flex flex-wrap items-center gap-2">
                                <Badge variant="secondary">{{
                                    film.genre
                                }}</Badge>
                                <span class="text-xs text-muted-foreground">
                                    {{ formatDuration(film.duration) }}
                                </span>
                            </div>
                            <CardTitle
                                class="line-clamp-2 text-base leading-snug"
                            >
                                {{ film.title }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="flex-1 px-4 pb-0">
                            <CardDescription class="line-clamp-3 text-sm">
                                {{ film.synopsis }}
                            </CardDescription>
                        </CardContent>
                        <CardFooter class="px-4 pb-4 pt-4">
                            <Button as-child class="w-full">
                                <Link :href="filmShow.url(film.id)"
                                    >Voir le film</Link
                                >
                            </Button>
                        </CardFooter>
                    </Card>
                </li>
            </ul>

            <div class="flex justify-center border-t border-border pt-8">
                <Button as-child size="lg" variant="outline">
                    <Link :href="filmsIndex.url()"
                        >Voir toutes les sorties</Link
                    >
                </Button>
            </div>
        </template>
    </div>
</template>