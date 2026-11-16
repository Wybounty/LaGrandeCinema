<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    formatSessionDate,
    formatSessionTime,
} from '@/lib/formatDuration';
import { formatMoney } from '@/lib/formatMoney';
import { index as filmsIndex } from '@/routes/films';
import type { ReservationSummary } from '@/types';

defineProps<{
    reservation: ReservationSummary;
}>();
</script>

<template>
    <Head title="Réservation confirmée" />

    <div class="mx-auto flex w-full max-w-lg flex-1 flex-col gap-6 px-4 py-6 md:px-6">
        <Card class="text-center">
            <CardHeader class="items-center space-y-3">
                <CheckCircle2 class="size-12 text-green-600" aria-hidden="true" />
                <CardTitle class="text-2xl">Réservation confirmée</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3 text-sm text-muted-foreground">
                <p>
                    Votre réservation a bien été prise en compte. Les billets QR
                    seront disponibles dans une prochaine version.
                </p>
                <p class="font-medium text-foreground">
                    {{ reservation.session.film_title }}
                </p>
                <p>
                    {{ formatSessionDate(reservation.session.starts_at) }} —
                    {{ formatSessionTime(reservation.session.starts_at) }}
                </p>
                <p>
                    {{ reservation.session.room_name }} —
                    {{ reservation.number_of_places }} place(s)
                </p>
                <p class="text-foreground">
                    Total payé (simulation) :
                    {{ formatMoney(reservation.total_amount) }}
                </p>
                <Button as-child class="mt-4 w-full">
                    <Link :href="filmsIndex.url()">Retour aux films</Link>
                </Button>
            </CardContent>
        </Card>
    </div>
</template>