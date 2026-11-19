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
import type { PaginatedFilms } from '@/types';

defineProps<{
    films: PaginatedFilms;
}>();
</script>

<template>
    <Head title="Films à l'affiche" />

    <div
        class="mx-auto flex w-full max-w-7xl flex-1 flex-col gap-8 px-4 py-6 md:px-6"
    >
        <Heading
            title="À l'affiche"
            description="Films grand public, cinéma d'auteur et événements : consultez les séances et les places encore disponibles avant de réserver en ligne."
        />

        <div
            v-if="films.data.length === 0"
            class="flex flex-col items-center justify-center rounded-xl border border-dashed border-sidebar-border/70 px-6 py-20 text-center dark:border-sidebar-border"
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
                <li v-for="film in films.data" :key="film.id">
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
                        <CardFooter class="px-4 pt-4 pb-4">
                            <Button as-child class="w-full">
                                <Link :href="filmShow.url(film.id)"
                                    >Voir le film</Link
                                >
                            </Button>
                        </CardFooter>
                    </Card>
                </li>
            </ul>

            <nav
                v-if="films.last_page > 1"
                class="flex flex-col items-center gap-3 border-t border-border pt-8"
                aria-label="Pagination des films"
            >
                <p class="text-sm text-muted-foreground">
                    Page {{ films.current_page }} sur {{ films.last_page }}
                </p>
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <Button
                        v-for="page in films.last_page"
                        :key="page"
                        as-child
                        size="sm"
                        :variant="
                            page === films.current_page ? 'default' : 'outline'
                        "
                    >
                        <Link
                            :href="filmsIndex.url({ query: { page } })"
                            preserve-scroll
                            >{{ page }}</Link
                        >
                    </Button>
                </div>
            </nav>
        </template>
    </div>
</template>
