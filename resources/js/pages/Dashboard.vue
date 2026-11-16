<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Ticket } from '@lucide/vue';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import { index as ticketsIndex } from '@/routes/tickets';
import type { Auth } from '@/types';

const page = usePage<{ auth: Auth }>();
const isSpectateur = computed(
    () => page.props.auth.user?.role === 'spectateur',
);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <h1 class="text-2xl font-semibold tracking-tight">Mon espace</h1>

        <Card
            v-if="isSpectateur"
            class="max-w-lg border-sidebar-border/70 dark:border-sidebar-border"
        >
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-lg">
                    <Ticket class="size-5" aria-hidden="true" />
                    Mes billets
                </CardTitle>
                <CardDescription>
                    Consultez vos billets dématérialisés et leurs QR codes après
                    confirmation de vos réservations.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Button as-child>
                    <Link :href="ticketsIndex.url()">Voir mes billets</Link>
                </Button>
            </CardContent>
        </Card>
    </div>
</template>