<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminPage from '@/components/admin/AdminPage.vue';
import ConfirmPostButton from '@/components/admin/ConfirmPostButton.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    formatDuration,
    formatSessionDate,
    formatSessionTime,
} from '@/lib/formatDuration';
import { formatMoney } from '@/lib/formatMoney';
import { reservationStatusLabel } from '@/lib/reservationStatus';
import { cancel, index } from '@/routes/admin/reservations';

defineProps<{
    reservation: {
        id: number;
        reference: string;
        status: string;
        number_of_places: number;
        total_amount: number;
        created_at: string | null;
        spectator: { name: string; email: string };
        film: { title: string; duration: number };
        session: { starts_at: string; ends_at: string };
        room_name: string;
        tickets: {
            id: number;
            place_number: number;
            status: string;
            generated_at: string;
        }[];
    };
    can: { cancel: boolean };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Réservations', href: index.url() },
            { title: 'Détail' },
        ],
    },
});
</script>

<template>
    <Head :title="`Réservation ${reservation.reference}`" />

    <AdminPage
        :title="reservation.reference"
        :description="reservationStatusLabel(reservation.status)"
    >
        <template #actions>
            <Button as-child variant="outline">
                <Link :href="index.url()">Retour à la liste</Link>
            </Button>
            <ConfirmPostButton
                v-if="can.cancel"
                :action-url="cancel.url(reservation.id)"
                label="Annuler la réservation"
                submit-label="Annuler la réservation"
                description="La réservation sera marquée comme annulée. Les places redeviennent disponibles pour les séances confirmées. Les billets restent archivés."
            />
        </template>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardContent class="space-y-4 pt-6 text-sm">
                    <h3 class="font-medium">Spectateur</h3>
                    <p>{{ reservation.spectator.name }}</p>
                    <p class="text-muted-foreground">
                        {{ reservation.spectator.email }}
                    </p>

                    <h3 class="pt-2 font-medium">Réservation</h3>
                    <dl class="grid gap-2">
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted-foreground">Statut</dt>
                            <dd>
                                {{ reservationStatusLabel(reservation.status) }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted-foreground">Places</dt>
                            <dd>{{ reservation.number_of_places }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted-foreground">Montant</dt>
                            <dd>
                                {{ formatMoney(reservation.total_amount) }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted-foreground">Créée le</dt>
                            <dd>
                                {{
                                    reservation.created_at
                                        ? new Date(
                                              reservation.created_at,
                                          ).toLocaleString('fr-CA')
                                        : '—'
                                }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>

            <Card>
                <CardContent class="space-y-4 pt-6 text-sm">
                    <h3 class="font-medium">Séance</h3>
                    <p>{{ reservation.film.title }}</p>
                    <p class="text-muted-foreground">
                        Durée : {{ formatDuration(reservation.film.duration) }}
                    </p>
                    <p>
                        {{ formatSessionDate(reservation.session.starts_at) }}
                    </p>
                    <p class="text-muted-foreground">
                        {{ formatSessionTime(reservation.session.starts_at) }}
                        →
                        {{ formatSessionTime(reservation.session.ends_at) }}
                    </p>
                    <p>Salle : {{ reservation.room_name }}</p>
                </CardContent>
            </Card>
        </div>

        <Card v-if="reservation.tickets.length > 0" class="mt-6">
            <CardContent class="space-y-4 pt-6">
                <h3 class="font-medium">Billets</h3>
                <div class="overflow-x-auto rounded-md border">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 text-left">
                            <tr>
                                <th class="px-4 py-3 font-medium">Place</th>
                                <th class="px-4 py-3 font-medium">Statut</th>
                                <th class="px-4 py-3 font-medium">Généré le</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="ticket in reservation.tickets"
                                :key="ticket.id"
                                class="border-t"
                            >
                                <td class="px-4 py-3">
                                    {{ ticket.place_number }}
                                </td>
                                <td class="px-4 py-3">{{ ticket.status }}</td>
                                <td class="px-4 py-3">
                                    {{
                                        new Date(
                                            ticket.generated_at,
                                        ).toLocaleString('fr-CA')
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </AdminPage>
</template>
