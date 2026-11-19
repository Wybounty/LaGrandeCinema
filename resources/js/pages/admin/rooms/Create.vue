<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AdminPage from '@/components/admin/AdminPage.vue';
import RoomFormFields from '@/components/admin/RoomFormFields.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { create, index, store } from '@/routes/admin/rooms';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Salles', href: index.url() },
            { title: 'Ajouter', href: create.url() },
        ],
    },
});
</script>

<template>
    <Head title="Ajouter une salle" />

    <AdminPage
        title="Ajouter une salle"
        description="Définissez le nom et la capacité."
    >
        <Card class="max-w-md">
            <CardContent class="pt-6">
                <Form
                    v-bind="store.form()"
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                    disable-while-processing
                >
                    <RoomFormFields :errors="errors" />
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
