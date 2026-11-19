<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AdminPage from '@/components/admin/AdminPage.vue';
import SessionFormFields from '@/components/admin/SessionFormFields.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { create, index, store } from '@/routes/admin/sessions';

defineProps<{
    films: { id: number; title: string; duration: number }[];
    rooms: { id: number; name: string }[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Séances', href: index.url() },
            { title: 'Programmer', href: create.url() },
        ],
    },
});
</script>

<template>
    <Head title="Programmer une séance" />

    <AdminPage
        title="Programmer une séance"
        description="Choisissez le film, la salle et les horaires."
    >
        <Card class="max-w-lg">
            <CardContent class="pt-6">
                <Form
                    v-bind="store.form()"
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                    disable-while-processing
                >
                    <SessionFormFields
                        :errors="errors"
                        :films="films"
                        :rooms="rooms"
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
