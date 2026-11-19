<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
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

    <div class="flex flex-1 flex-col gap-12">
        <section
            class="relative isolate overflow-hidden border-b border-border/80"
            aria-label="Le Grand Cinéma"
        >
            <div
                class="absolute inset-0 bg-cover bg-center"
                style="background-image: url('/images/cinema-hero.jpg')"
            />
            <div
                class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/70 to-black/50"
            />
            <div
                class="relative mx-auto flex max-w-7xl flex-col gap-6 px-4 py-16 md:px-6 md:py-24"
            >
                <p
                    class="text-sm font-medium tracking-wide text-white/80 uppercase"
                >
                    Réservation en ligne
                </p>
                <h1
                    class="max-w-2xl text-3xl font-semibold tracking-tight text-white sm:text-4xl md:text-5xl"
                >
                    Le Grand Cinéma
                </h1>
                <p class="max-w-2xl text-base leading-relaxed text-white/90">
                    Plus de cinquante ans au service de la culture locale, avec
                    une programmation variée et la possibilité de réserver vos
                    places en quelques clics, y compris lors des périodes de
                    forte affluence.
                </p>
                <div class="flex flex-wrap gap-3">
                    <Button as-child size="lg">
                        <Link :href="filmsIndex.url()"
                            >Voir la programmation</Link
                        >
                    </Button>
                </div>
            </div>
        </section>

        <div
            class="mx-auto flex w-full max-w-7xl flex-col gap-12 px-4 pb-12 md:px-6"
        >
            <section class="space-y-6" aria-labelledby="home-films-heading">
                <div class="space-y-2">
                    <h2
                        id="home-films-heading"
                        class="text-2xl font-semibold tracking-tight"
                    >
                        Prochaines séances à l'affiche
                    </h2>
                    <p class="max-w-2xl text-sm text-muted-foreground">
                        Explorez les films programmés et consultez les places
                        encore disponibles pour chaque séance.
                    </p>
                </div>

                <div
                    v-if="films.length === 0"
                    class="flex flex-col items-center justify-center rounded-xl border border-dashed border-sidebar-border/70 px-6 py-16 text-center dark:border-sidebar-border"
                >
                    <p class="text-xl font-semibold tracking-tight">
                        Aucun film à l'affiche
                    </p>
                    <p class="mt-3 max-w-md text-sm text-muted-foreground">
                        Aucune séance future n'est programmée pour le moment.
                        Revenez bientôt pour découvrir la prochaine
                        programmation.
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
                                <div
                                    class="aspect-[2/3] overflow-hidden bg-muted"
                                >
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
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <Badge variant="secondary">{{
                                            film.genre
                                        }}</Badge>
                                        <span
                                            class="text-xs text-muted-foreground"
                                        >
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
                                    <CardDescription
                                        class="line-clamp-3 text-sm"
                                    >
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

                    <div
                        class="flex justify-center border-t border-border pt-8"
                    >
                        <Button as-child size="lg" variant="outline">
                            <Link :href="filmsIndex.url()"
                                >Voir tous les films</Link
                            >
                        </Button>
                    </div>
                </template>
            </section>
        </div>
    </div>
</template>
