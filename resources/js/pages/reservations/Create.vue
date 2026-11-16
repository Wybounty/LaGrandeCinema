<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import {
    formatSessionDate,
    formatSessionTime,
} from '@/lib/formatDuration';
import { formatMoney } from '@/lib/formatMoney';
import { show as filmShow } from '@/routes/films';
import { store } from '@/routes/reservations';

type SessionInfo = {
    id: number;
    starts_at: string;
    ends_at: string;
    room_name: string;
    film: { id: number; title: string };
};

const props = defineProps<{
    session: SessionInfo;
    remainingSeats: number;
    unitPrice: number;
}>();

const numberOfPlaces = ref(1);

const maxPlaces = computed(() => Math.min(props.remainingSeats, 20));

const estimatedTotal = computed(
    () => numberOfPlaces.value * props.unitPrice,
);
</script>

<template>
    <Head :title="`Réserver — ${session.film.title}`" />

    <div class="mx-auto flex w-full max-w-lg flex-1 flex-col gap-6 px-4 py-6 md:px-6">
        <Card>
            <CardHeader>
                <CardTitle>Réserver des places</CardTitle>
                <CardDescription>
                    Vérifiez le film et la séance sélectionnés.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-3 text-sm">
                <p class="text-base font-medium">
                    {{ session.film.title }}
                </p>
                <p>
                    {{ formatSessionDate(session.starts_at) }} —
                    {{ formatSessionTime(session.starts_at) }} →
                    {{ formatSessionTime(session.ends_at) }}
                </p>
                <p>Salle : {{ session.room_name }}</p>
                <p class="text-muted-foreground">
                    Places disponibles : {{ remainingSeats }}
                </p>
                <p class="text-muted-foreground">
                    Prix unitaire : {{ formatMoney(unitPrice) }}
                </p>
            </CardContent>
        </Card>

        <div
            v-if="remainingSeats === 0"
            class="rounded-lg border border-dashed border-border px-4 py-8 text-center text-sm"
        >
            <p class="font-medium">Cette séance est complète.</p>
            <Button as-child class="mt-4" variant="outline">
                <Link :href="filmShow.url(session.film.id)">
                    Retour au film
                </Link>
            </Button>
        </div>

        <Form
            v-else
            v-bind="store.form()"
            v-slot="{ errors, processing }"
            class="space-y-4"
            disable-while-processing
        >
            <input type="hidden" name="cinema_session_id" :value="session.id" />

            <div class="space-y-2">
                <Label for="number_of_places">Nombre de places</Label>
                <Input
                    id="number_of_places"
                    v-model.number="numberOfPlaces"
                    name="number_of_places"
                    type="number"
                    :min="1"
                    :max="maxPlaces"
                    required
                />
                <InputError :message="errors.number_of_places" />
                <InputError :message="errors.cinema_session_id" />
            </div>

            <Card class="bg-muted/40 py-4 shadow-none">
                <CardContent class="px-4 text-sm">
                    <p>
                        Total estimé :
                        <span class="font-semibold">{{
                            formatMoney(estimatedTotal)
                        }}</span>
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Montant recalculé côté serveur avant le récapitulatif
                        et le paiement.
                    </p>
                </CardContent>
            </Card>

            <div class="flex flex-wrap gap-2">
                <Button type="submit" :disabled="processing">
                    <Spinner v-if="processing" class="mr-2" />
                    Continuer vers le récapitulatif
                </Button>
                <Button as-child variant="outline">
                    <Link :href="filmShow.url(session.film.id)">Annuler</Link>
                </Button>
            </div>
        </Form>
    </div>
</template>