<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { confirmation, show } from '@/routes/reservations';
import type { ReservationSummary } from '@/types';

const props = defineProps<{
    reservation: ReservationSummary;
}>();

let pollTimer: ReturnType<typeof setInterval> | undefined;

onMounted(() => {
    if (props.reservation.status === 'confirmed') {
        router.visit(
            confirmation.url(props.reservation.id, { query: { paid: 1 } }),
        );
        return;
    }

    pollTimer = setInterval(() => {
        router.reload();
    }, 2500);
});

onUnmounted(() => {
    if (pollTimer !== undefined) {
        clearInterval(pollTimer);
    }
});
</script>

<template>
    <Head title="Paiement en cours de traitement" />

    <div
        class="mx-auto flex w-full max-w-lg flex-1 flex-col gap-6 px-4 py-6 md:px-6"
    >
        <Card class="border-emerald-500/40 bg-emerald-500/5">
            <CardHeader>
                <CardTitle>Paiement reçu par Stripe</CardTitle>
                <CardDescription>
                    Nous validons votre paiement. Dès que la réservation est
                    confirmée, vous serez redirigé automatiquement et vos
                    billets seront envoyés par e-mail.
                </CardDescription>
            </CardHeader>
            <CardContent class="flex flex-col items-center gap-4">
                <Spinner class="size-8" />
                <p class="text-center text-sm text-muted-foreground">
                    Cette page se met à jour automatiquement…
                </p>
                <Button as-child variant="ghost" class="w-full">
                    <Link :href="show(reservation.id)"
                        >Retour au récapitulatif</Link
                    >
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
