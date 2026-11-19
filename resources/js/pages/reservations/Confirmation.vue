<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2 } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { formatSessionDate, formatSessionTime } from '@/lib/formatDuration';
import { formatMoney } from '@/lib/formatMoney';
import { index as filmsIndex } from '@/routes/films';
import { index as ticketsIndex } from '@/routes/tickets';
import type { ReservationSummary } from '@/types';

defineProps<{
    reservation: ReservationSummary;
}>();
</script>

<template>
    <Head title="Réservation confirmée" />

    <div
        class="mx-auto flex w-full max-w-lg flex-1 flex-col gap-6 px-4 py-6 md:px-6"
    >
        <Card class="text-center">
            <CardHeader class="items-center space-y-3">
                <CheckCircle2
                    class="size-12 text-green-600"
                    aria-hidden="true"
                />
                <CardTitle class="text-2xl">Réservation confirmée</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3 text-sm text-muted-foreground">
                <p>
                    Votre réservation est confirmée. Vos billets avec QR code
                    sont disponibles dans Mes billets et une copie vous a été
                    envoyée par e-mail.
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
                    Total payé : {{ formatMoney(reservation.total_amount) }}
                </p>
                <div class="mt-4 flex w-full flex-col gap-2">
                    <Button as-child class="w-full">
                        <Link :href="ticketsIndex.url()">Mes billets</Link>
                    </Button>
                    <Button as-child class="w-full" variant="outline">
                        <Link :href="filmsIndex.url()">Retour aux films</Link>
                    </Button>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
