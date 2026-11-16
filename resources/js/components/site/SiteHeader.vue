<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Menu, X } from '@lucide/vue';
import { computed, ref } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import { Button } from '@/components/ui/button';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard, home, login, register } from '@/routes';
import { index as films } from '@/routes/films';
import type { InertiaLinkProps } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth.user);
const mobileOpen = ref(false);
const { isCurrentUrl, whenCurrentUrl } = useCurrentUrl();

const navItems = [
    { title: 'Accueil', href: home() },
    { title: 'Films', href: films() },
] as const;

const linkClass = (href: NonNullable<InertiaLinkProps['href']>) =>
    [
        'rounded-md px-3 py-2 text-sm font-medium transition-colors',
        whenCurrentUrl(
            href,
            'bg-primary/10 text-primary',
            'text-muted-foreground hover:bg-muted hover:text-foreground',
        ),
    ].join(' ');
</script>

<template>
    <header
        class="sticky top-0 z-50 border-b border-border/80 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80"
    >
        <div
            class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 md:px-6"
        >
            <Link
                :href="home()"
                class="flex items-center gap-2 rounded-md outline-none focus-visible:ring-2 focus-visible:ring-ring"
            >
                <AppLogo />
            </Link>

            <nav class="hidden items-center gap-1 md:flex" aria-label="Principal">
                <Link
                    v-for="item in navItems"
                    :key="item.title"
                    :href="item.href"
                    :class="linkClass(item.href)"
                    :aria-current="isCurrentUrl(item.href) ? 'page' : undefined"
                >
                    {{ item.title }}
                </Link>
            </nav>

            <div class="hidden items-center gap-2 md:flex">
                <template v-if="user">
                    <Button variant="outline" as-child>
                        <Link :href="dashboard()">Mon espace</Link>
                    </Button>
                </template>
                <template v-else>
                    <Button variant="ghost" as-child>
                        <Link :href="login()">Connexion</Link>
                    </Button>
                    <Button as-child>
                        <Link :href="register()">Inscription</Link>
                    </Button>
                </template>
            </div>

            <Button
                variant="ghost"
                size="icon"
                class="md:hidden"
                :aria-expanded="mobileOpen"
                aria-controls="site-mobile-nav"
                @click="mobileOpen = !mobileOpen"
            >
                <X v-if="mobileOpen" class="size-5" />
                <Menu v-else class="size-5" />
                <span class="sr-only">Menu</span>
            </Button>
        </div>

        <div
            v-show="mobileOpen"
            id="site-mobile-nav"
            class="border-t border-border/80 px-4 py-4 md:hidden"
        >
            <nav class="flex flex-col gap-1" aria-label="Principal mobile">
                <Link
                    v-for="item in navItems"
                    :key="`${item.title}-mobile`"
                    :href="item.href"
                    :class="linkClass(item.href)"
                    @click="mobileOpen = false"
                >
                    {{ item.title }}
                </Link>
            </nav>
            <div class="mt-4 flex flex-col gap-2 border-t border-border pt-4">
                <template v-if="user">
                    <Button variant="outline" as-child class="w-full">
                        <Link :href="dashboard()" @click="mobileOpen = false">
                            Mon espace
                        </Link>
                    </Button>
                </template>
                <template v-else>
                    <Button variant="ghost" as-child class="w-full">
                        <Link :href="login()" @click="mobileOpen = false">
                            Connexion
                        </Link>
                    </Button>
                    <Button as-child class="w-full">
                        <Link :href="register()" @click="mobileOpen = false">
                            Inscription
                        </Link>
                    </Button>
                </template>
            </div>
        </div>
    </header>
</template>