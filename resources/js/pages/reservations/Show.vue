<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import {
    formatSessionDate,
    formatSessionTime,
} from '@/lib/formatDuration';
import { formatMoney } from '@/lib/formatMoney';
import { checkout } from '@/routes/reservations';
import type { ReservationSummary } from '@/types';

defineProps<{
    reservation: ReservationSummary;
    unitPrice: number;
}>();
</script>

<template>
    <Head title="Récapitulatif" />

    <div class="mx-auto flex w-full max-w-lg flex-1 flex-col gap-6 px-4 py-6 md:px-6">
        <Card>
            <CardHeader>
                <CardTitle>Récapitulatif</CardTitle>
                <CardDescription>
                    Vérifiez les détails avant le paiement sécurisé sur Stripe.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-3 text-sm">
                <p class="text-base font-semibold">
                    {{ reservation.session.film_title }}
                </p>
                <p>
                    {{ formatSessionDate(reservation.session.starts_at) }} —
                    {{ formatSessionTime(reservation.session.starts_at) }}
                </p>
                <p>{{ reservation.session.room_name }}</p>
                <p>{{ reservation.number_of_places }} place(s)</p>
                <p>Prix unitaire : {{ formatMoney(unitPrice) }}</p>
                <p class="text-lg font-semibold">
                    Total : {{ formatMoney(reservation.total_amount) }}
                </p>
            </CardContent>
        </Card>

        <Card
            v-if="reservation.status === 'pending'"
            class="border-dashed"
        >
            <CardHeader>
                <CardTitle class="text-base">Paiement</CardTitle>
                <CardDescription>
                    Vous serez redirigé vers Stripe pour régler votre
                    réservation. La confirmation définitive intervient après
                    validation du paiement par notre serveur.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <Form
                    v-bind="checkout.form(reservation.id)"
                    v-slot="{ errors, processing }"
                    disable-while-processing
                >
                    <InputError class="mb-3" :message="errors.payment" />
                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="processing"
                    >
                        <Spinner v-if="processing" class="mr-2" />
                        Payer avec Stripe
                    </Button>
                </Form>
            </CardContent>
        </Card>
    </div>
</template>