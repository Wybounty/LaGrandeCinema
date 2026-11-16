<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AdminPage from '@/components/admin/AdminPage.vue';
import RoomFormFields from '@/components/admin/RoomFormFields.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { index, update } from '@/routes/admin/rooms';

defineProps<{
    room: { id: number; name: string; capacity: number };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Salles', href: index.url() },
            { title: 'Modifier' },
        ],
    },
});
</script>

<template>
    <Head :title="`Modifier — ${room.name}`" />

    <AdminPage title="Modifier la salle" :description="room.name">
        <Card class="max-w-md">
            <CardContent class="pt-6">
                <Form
                    v-bind="update.form(room.id)"
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                    disable-while-processing
                >
                    <RoomFormFields :errors="errors" :defaults="room" />
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