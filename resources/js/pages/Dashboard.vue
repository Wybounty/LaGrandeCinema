<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Ticket } from '@lucide/vue';
import { computed } from 'vue';
import AdminStatisticsPanel from '@/components/admin/AdminStatisticsPanel.vue';
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

type StatisticsPayload = {
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
        points: {
            date: string;
            reservations: number;
            places: number;
        }[];
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
};

const page = usePage<{ auth: Auth; statistics: StatisticsPayload | null }>();

const isSpectateur = computed(
    () => page.props.auth.user?.role === 'spectateur',
);

const statistics = computed(() => page.props.statistics);

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

    <div class="flex flex-1 flex-col gap-6 p-4">
        <h1 class="text-2xl font-semibold tracking-tight">
            {{ statistics ? 'Tableau de bord' : 'Mon espace' }}
        </h1>

        <AdminStatisticsPanel v-if="statistics" v-bind="statistics" />

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