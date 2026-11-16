<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Ticket } from '@lucide/vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    formatSessionDate,
    formatSessionTime,
} from '@/lib/formatDuration';
import { index as ticketsIndex } from '@/routes/tickets';
import type { TicketSummary } from '@/types';

defineProps<{
    tickets: TicketSummary[];
    cinemaName: string;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Mes billets',
                href: ticketsIndex.url(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Mes billets" />

    <div class="flex flex-1 flex-col gap-4 p-4">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold tracking-tight">Mes billets</h1>
            <p class="text-sm text-muted-foreground">
                Retrouvez ici vos billets pour {{ cinemaName }}, avec leur QR
                code personnel.
            </p>
        </div>

        <div
            v-if="tickets.length === 0"
            class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-border px-6 py-16 text-center"
        >
            <Ticket class="mb-4 size-10 text-muted-foreground" aria-hidden="true" />
            <p class="font-medium">Aucun billet pour le moment</p>
            <p class="mt-2 max-w-sm text-sm text-muted-foreground">
                Après confirmation d'une réservation, vos billets apparaîtront
                ici et vous recevrez un e-mail de récapitulatif.
            </p>
        </div>

        <ul v-else class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <li v-for="ticket in tickets" :key="ticket.id">
                <Card class="h-full">
                    <CardHeader class="space-y-2">
                        <CardTitle class="text-base leading-snug">
                            {{ ticket.film_title }}
                        </CardTitle>
                        <CardDescription>
                            Place {{ ticket.place_number }} /
                            {{ ticket.places_total }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 text-sm">
                        <p>
                            {{ formatSessionDate(ticket.starts_at) }} —
                            {{ formatSessionTime(ticket.starts_at) }}
                        </p>
                        <p class="text-muted-foreground">
                            Salle {{ ticket.room_name }}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            Cinéma : {{ cinemaName }}
                        </p>
                        <p
                            class="break-all font-mono text-xs text-muted-foreground"
                        >
                            {{ ticket.qr_code }}
                        </p>
                        <div
                            class="flex justify-center rounded-lg border bg-white p-3 dark:bg-background"
                        >
                            <img
                                :src="ticket.qr_image_url"
                                :alt="`QR code billet ${ticket.place_number}`"
                                class="size-40"
                                width="160"
                                height="160"
                            />
                        </div>
                    </CardContent>
                </Card>
            </li>
        </ul>
    </div>
</template>