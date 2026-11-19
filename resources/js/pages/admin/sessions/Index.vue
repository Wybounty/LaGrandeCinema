<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { ref } from 'vue';
import AdminPage from '@/components/admin/AdminPage.vue';
import ConfirmDeleteButton from '@/components/admin/ConfirmDeleteButton.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { formatSessionDate, formatSessionTime } from '@/lib/formatDuration';
import { handlePosterError, posterSrc } from '@/lib/posterFallback';
import { cn } from '@/lib/utils';
import { create, edit, index, destroy } from '@/routes/admin/sessions';

type SessionRow = {
    id: number;
    starts_at: string;
    ends_at: string;
    film: { id: number; title: string; poster: string | null };
    room: { id: number; name: string };
    can: { delete: boolean };
};

type Paginated = {
    data: SessionRow[];
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    sessions: Paginated;
    filters: {
        search: string | null;
        film_id: number | null;
        room_id: number | null;
        session_date: string | null;
    };
    films: { id: number; title: string; duration: number }[];
    rooms: { id: number; name: string }[];
}>();

const search = ref(props.filters.search ?? '');
const filmId = ref(props.filters.film_id?.toString() ?? '');
const roomId = ref(props.filters.room_id?.toString() ?? '');
const sessionDate = ref(props.filters.session_date ?? '');

const applyFilters = useDebounceFn(() => {
    router.get(
        index.url({
            query: {
                search: search.value || undefined,
                film_id: filmId.value || undefined,
                room_id: roomId.value || undefined,
                session_date: sessionDate.value || undefined,
            },
        }),
        {},
        { preserveState: true, replace: true },
    );
}, 300);

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Séances', href: index.url() }],
    },
});
</script>

<template>
    <Head title="Gestion des séances" />

    <AdminPage
        title="Gestion des séances"
        description="Programmez les projections par salle et horaire."
    >
        <template #actions>
            <Button as-child>
                <Link :href="create.url()">Programmer une séance</Link>
            </Button>
        </template>

        <Card>
            <CardContent class="space-y-4 pt-6">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="grid gap-2">
                        <Label for="search">Recherche</Label>
                        <Input
                            id="search"
                            v-model="search"
                            placeholder="Titre du film…"
                            @update:model-value="applyFilters"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="filter-film">Film</Label>
                        <select
                            id="filter-film"
                            v-model="filmId"
                            :class="
                                cn(
                                    'h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs',
                                )
                            "
                            @change="applyFilters"
                        >
                            <option value="">Tous les films</option>
                            <option
                                v-for="film in films"
                                :key="film.id"
                                :value="film.id"
                            >
                                {{ film.title }}
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="filter-room">Salle</Label>
                        <select
                            id="filter-room"
                            v-model="roomId"
                            :class="
                                cn(
                                    'h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs',
                                )
                            "
                            @change="applyFilters"
                        >
                            <option value="">Toutes les salles</option>
                            <option
                                v-for="room in rooms"
                                :key="room.id"
                                :value="room.id"
                            >
                                {{ room.name }}
                            </option>
                        </select>
                    </div>
                    <div class="grid gap-2">
                        <Label for="filter-date">Date</Label>
                        <Input
                            id="filter-date"
                            v-model="sessionDate"
                            type="date"
                            @change="applyFilters"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto rounded-md border">
                    <table class="w-full min-w-[880px] text-sm">
                        <thead class="bg-muted/50 text-left">
                            <tr>
                                <th class="px-4 py-3 font-medium">Affiche</th>
                                <th class="px-4 py-3 font-medium">Film</th>
                                <th class="px-4 py-3 font-medium">Salle</th>
                                <th class="px-4 py-3 font-medium">Date</th>
                                <th class="px-4 py-3 font-medium">Horaire</th>
                                <th class="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="session in sessions.data"
                                :key="session.id"
                                class="border-t"
                            >
                                <td class="px-4 py-3">
                                    <img
                                        :src="posterSrc(session.film.poster)"
                                        :alt="session.film.title"
                                        class="h-16 w-11 rounded object-cover"
                                        @error="handlePosterError"
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    {{ session.film.title }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ session.room.name }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ formatSessionDate(session.starts_at) }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ formatSessionTime(session.starts_at) }}
                                    →
                                    {{ formatSessionTime(session.ends_at) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <Button
                                            as-child
                                            size="sm"
                                            variant="outline"
                                        >
                                            <Link :href="edit.url(session.id)">
                                                Modifier
                                            </Link>
                                        </Button>
                                        <ConfirmDeleteButton
                                            v-if="session.can.delete"
                                            :destroy-url="
                                                destroy.url(session.id)
                                            "
                                            description="La séance sera définitivement supprimée."
                                        />
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="sessions.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-4 py-8 text-center text-muted-foreground"
                                >
                                    Aucune séance trouvée.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <nav
                    v-if="sessions.links.length > 3"
                    class="flex flex-wrap gap-1"
                    aria-label="Pagination"
                >
                    <template v-for="(link, i) in sessions.links" :key="i">
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
