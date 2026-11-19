<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { ref } from 'vue';
import AdminPage from '@/components/admin/AdminPage.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatSessionDate, formatSessionTime } from '@/lib/formatDuration';
import { formatMoney } from '@/lib/formatMoney';
import { reservationStatusLabel } from '@/lib/reservationStatus';
import { cn } from '@/lib/utils';
import { index, show } from '@/routes/admin/reservations';

type Row = {
    id: number;
    reference: string;
    spectator_name: string;
    spectator_email: string;
    film_title: string;
    session_starts_at: string;
    room_name: string;
    number_of_places: number;
    total_amount: number;
    status: string;
    created_at: string;
};

type Paginated = {
    data: Row[];
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    reservations: Paginated;
    filters: {
        search: string | null;
        reference: string | null;
        status: string | null;
        reservation_date: string | null;
    };
}>();

const search = ref(props.filters.search ?? '');
const reference = ref(props.filters.reference ?? '');
const status = ref(props.filters.status ?? '');
const reservationDate = ref(props.filters.reservation_date ?? '');

const applyFilters = useDebounceFn(() => {
    router.get(
        index.url({
            query: {
                search: search.value || undefined,
                reference: reference.value || undefined,
                status: status.value || undefined,
                reservation_date: reservationDate.value || undefined,
            },
        }),
        {},
        { preserveState: true, replace: true },
    );
}, 300);

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Réservations', href: index.url() }],
    },
});
</script>

<template>
    <Head title="Gestion des réservations" />

    <AdminPage
        title="Gestion des réservations"
        description="Consultez et annulez les réservations du cinéma."
    >
        <Card>
            <CardContent class="space-y-4 pt-6">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="grid gap-2">
                        <Label for="search">Spectateur</Label>
                        <Input
                            id="search"
                            v-model="search"
                            placeholder="Nom ou e-mail…"
                            @update:model-value="applyFilters"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="reference">Référence</Label>
                        <Input
                            id="reference"
                            v-model="reference"
                            placeholder="R-000042"
                            @update:model-value="applyFilters"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="filter-status">Statut</Label>
                        <select
                            id="filter-status"
                            v-model="status"
                            :class="
                                cn(
                                    'h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs',
                                )
                            "
                            @change="applyFilters"
                        >
                            <option value="">Tous</option>
                            <option value="pending">En attente</option>
                            <option value="confirmed">Confirmée</option>
                            <option value="cancelled">Annulée</option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="reservation-date"
                            >Date de réservation</Label
                        >
                        <Input
                            id="reservation-date"
                            v-model="reservationDate"
                            type="date"
                            @change="applyFilters"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto rounded-md border">
                    <table class="w-full min-w-[1100px] text-sm">
                        <thead class="bg-muted/50 text-left">
                            <tr>
                                <th class="px-3 py-3 font-medium">Réf.</th>
                                <th class="px-3 py-3 font-medium">
                                    Spectateur
                                </th>
                                <th class="px-3 py-3 font-medium">Film</th>
                                <th class="px-3 py-3 font-medium">Séance</th>
                                <th class="px-3 py-3 font-medium">Salle</th>
                                <th class="px-3 py-3 font-medium">Places</th>
                                <th class="px-3 py-3 font-medium">Montant</th>
                                <th class="px-3 py-3 font-medium">Statut</th>
                                <th class="px-3 py-3 font-medium">
                                    Réservé le
                                </th>
                                <th class="px-3 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="row in reservations.data"
                                :key="row.id"
                                class="border-t"
                            >
                                <td class="px-3 py-3 font-mono text-xs">
                                    {{ row.reference }}
                                </td>
                                <td class="px-3 py-3">
                                    <div class="font-medium">
                                        {{ row.spectator_name }}
                                    </div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ row.spectator_email }}
                                    </div>
                                </td>
                                <td class="px-3 py-3">{{ row.film_title }}</td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    {{
                                        formatSessionDate(row.session_starts_at)
                                    }}
                                    <br />
                                    <span class="text-xs text-muted-foreground">
                                        {{
                                            formatSessionTime(
                                                row.session_starts_at,
                                            )
                                        }}
                                    </span>
                                </td>
                                <td class="px-3 py-3">{{ row.room_name }}</td>
                                <td class="px-3 py-3">
                                    {{ row.number_of_places }}
                                </td>
                                <td class="px-3 py-3">
                                    {{ formatMoney(row.total_amount) }}
                                </td>
                                <td class="px-3 py-3">
                                    {{ reservationStatusLabel(row.status) }}
                                </td>
                                <td class="px-3 py-3 whitespace-nowrap">
                                    {{
                                        row.created_at
                                            ? new Date(
                                                  row.created_at,
                                              ).toLocaleString('fr-CA')
                                            : '—'
                                    }}
                                </td>
                                <td class="px-3 py-3">
                                    <Button
                                        as-child
                                        size="sm"
                                        variant="outline"
                                    >
                                        <Link :href="show.url(row.id)">
                                            Détail
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                            <tr v-if="reservations.data.length === 0">
                                <td
                                    colspan="10"
                                    class="px-4 py-8 text-center text-muted-foreground"
                                >
                                    Aucune réservation trouvée.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <nav
                    v-if="reservations.links.length > 3"
                    class="flex flex-wrap gap-1"
                    aria-label="Pagination"
                >
                    <template v-for="(link, i) in reservations.links" :key="i">
                        <Button
                            v-if="link.url"
                            as-child
                            size="sm"
                            :variant="link.active ? 'default' : 'outline'"
                        >
                            <Link :href="link.url" preserve-state>
                                <span v-html="link.label" />
                            </Link>
                        </Button>
                        <span
                            v-else
                            class="inline-flex items-center px-2 text-muted-foreground"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </CardContent>
        </Card>
    </AdminPage>
</template>
