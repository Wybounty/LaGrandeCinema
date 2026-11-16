<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Clock, Clapperboard } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { formatDuration } from '@/lib/formatDuration';
import { handlePosterError, posterSrc } from '@/lib/posterFallback';
import { index as filmsIndex } from '@/routes/films';
import type { Film } from '@/types';

const props = defineProps<{
    film: Film;
}>();

</script>

<template>
    <Head :title="props.film.title" />

    <div class="mx-auto flex w-full max-w-6xl flex-1 flex-col gap-6 px-4 py-6 md:px-6">
        <Button
            variant="ghost"
            as-child
            class="-ml-2 w-fit self-start text-muted-foreground"
        >
            <Link :href="filmsIndex()">
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
                        <p class="text-base leading-relaxed text-muted-foreground">
                            {{ props.film.synopsis }}
                        </p>
                    </section>
                </CardContent>
            </Card>
        </article>
    </div>
</template>