<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AdminPage from '@/components/admin/AdminPage.vue';
import SessionFormFields from '@/components/admin/SessionFormFields.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { index, update } from '@/routes/admin/sessions';

defineProps<{
    session: {
        id: number;
        film_id: number;
        room_id: number;
        session_date: string;
        start_time: string;
        end_time: string;
    };
    films: { id: number; title: string; duration: number }[];
    rooms: { id: number; name: string }[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Séances', href: index.url() },
            { title: 'Modifier' },
        ],
    },
});
</script>

<template>
    <Head title="Modifier la séance" />

    <AdminPage title="Modifier la séance" description="Ajustez le film, la salle ou les horaires.">
        <Card class="max-w-lg">
            <CardContent class="pt-6">
                <Form
                    v-bind="update.form(session.id)"
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                    disable-while-processing
                >
                    <SessionFormFields
                        :errors="errors"
                        :films="films"
                        :rooms="rooms"
                        :defaults="session"
                    />
                    <div class="flex flex-wrap gap-2">
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" class="mr-2" />
                            Enregistrer
                        </Button>
                        <Button as-child variant="outline">
                            <Link :href="index.url()">Annuler</Link>
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </AdminPage>
</template>