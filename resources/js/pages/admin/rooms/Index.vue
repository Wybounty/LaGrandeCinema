<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminPage from '@/components/admin/AdminPage.vue';
import ConfirmDeleteButton from '@/components/admin/ConfirmDeleteButton.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { create, edit, index, destroy } from '@/routes/admin/rooms';

type RoomRow = {
    id: number;
    name: string;
    capacity: number;
    cinema_sessions_count: number;
    can: { delete: boolean };
};

type Paginated = {
    data: RoomRow[];
    links: { url: string | null; label: string; active: boolean }[];
};

defineProps<{ rooms: Paginated }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Salles', href: index.url() }],
    },
});
</script>

<template>
    <Head title="Gestion des salles" />

    <AdminPage
        title="Gestion des salles"
        description="Consultez et gérez les salles de projection."
    >
        <template #actions>
            <Button as-child>
                <Link :href="create.url()">Ajouter une salle</Link>
            </Button>
        </template>

        <Card>
            <CardContent class="space-y-4 pt-6">
                <div class="overflow-x-auto rounded-md border">
                    <table class="w-full min-w-[480px] text-sm">
                        <thead class="bg-muted/50 text-left">
                            <tr>
                                <th class="px-4 py-3 font-medium">Nom</th>
                                <th class="px-4 py-3 font-medium">Capacité</th>
                                <th class="px-4 py-3 font-medium">Séances</th>
                                <th class="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="room in rooms.data"
                                :key="room.id"
                                class="border-t"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ room.name }}
                                </td>
                                <td class="px-4 py-3">{{ room.capacity }}</td>
                                <td class="px-4 py-3">
                                    {{ room.cinema_sessions_count }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <Button
                                            as-child
                                            size="sm"
                                            variant="outline"
                                        >
                                            <Link :href="edit.url(room.id)">
                                                Modifier
                                            </Link>
                                        </Button>
                                        <ConfirmDeleteButton
                                            v-if="room.can.delete"
                                            :destroy-url="destroy.url(room.id)"
                                            description="La salle sera définitivement supprimée."
                                        />
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="rooms.data.length === 0">
                                <td
                                    colspan="4"
                                    class="px-4 py-8 text-center text-muted-foreground"
                                >
                                    Aucune salle enregistrée.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <nav
                    v-if="rooms.links.length > 3"
                    class="flex flex-wrap gap-1"
                    aria-label="Pagination"
                >
                    <template v-for="(link, i) in rooms.links" :key="i">
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
