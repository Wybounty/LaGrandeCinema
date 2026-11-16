<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { home } from '@/routes';
import { index as films } from '@/routes/films';

type CinemaInfo = {
    name: string;
    tagline: string;
    email: string;
    phone: string;
    address: string;
    postal_code: string;
    city: string;
};

const page = usePage();
const cinema = computed(() => page.props.cinema as CinemaInfo);
const year = new Date().getFullYear();
</script>

<template>
    <footer class="mt-auto border-t border-border/80 bg-muted/30">
        <div
            class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-10 md:flex-row md:items-center md:justify-between md:px-6"
        >
            <div class="space-y-2">
                <p class="text-sm font-semibold tracking-tight">
                    {{ cinema.name }}
                </p>
                <p class="max-w-sm text-sm text-muted-foreground">
                    {{ cinema.tagline }}
                </p>
                <p class="text-xs text-muted-foreground">
                    {{ cinema.address }}, {{ cinema.postal_code }}
                    {{ cinema.city }} —
                    <a
                        :href="`mailto:${cinema.email}`"
                        class="underline-offset-2 hover:underline"
                        >{{ cinema.email }}</a
                    >
                    · {{ cinema.phone }}
                </p>
            </div>

            <nav
                class="flex flex-wrap gap-x-6 gap-y-2 text-sm"
                aria-label="Pied de page"
            >
                <Link
                    :href="home()"
                    class="text-muted-foreground transition-colors hover:text-foreground"
                >
                    Accueil
                </Link>
                <Link
                    :href="films()"
                    class="text-muted-foreground transition-colors hover:text-foreground"
                >
                    Films à l'affiche
                </Link>
            </nav>
        </div>

        <div class="border-t border-border/60 py-4 text-center text-xs text-muted-foreground">
            © {{ year }} {{ cinema.name }}. Tous droits réservés.
        </div>
    </footer>
</template>