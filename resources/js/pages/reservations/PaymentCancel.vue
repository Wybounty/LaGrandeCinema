<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { show } from '@/routes/reservations';
import type { ReservationSummary } from '@/types';

defineProps<{
    reservation: ReservationSummary;
}>();
</script>

<template>
    <Head title="Paiement annulé" />

    <div class="mx-auto flex w-full max-w-lg flex-1 flex-col gap-6 px-4 py-6 md:px-6">
        <Card class="border-amber-500/40 bg-amber-500/5">
            <CardHeader>
                <CardTitle>Paiement non finalisé</CardTitle>
                <CardDescription>
                    Vous avez quitté Stripe sans finaliser le paiement. Votre
                    réservation reste en attente et aucun montant n'a été
                    débité.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Button as-child class="w-full">
                    <Link :href="show(reservation.id)">
                        Reprendre le paiement
                    </Link>
                </Button>
            </CardContent>
        </Card>
    </div>
</template>