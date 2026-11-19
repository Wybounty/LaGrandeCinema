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
import { formatDuration } from '@/lib/formatDuration';
import { handlePosterError, posterSrc } from '@/lib/posterFallback';
import { cn } from '@/lib/utils';
import { create, edit, index, destroy } from '@/routes/admin/films';

type FilmRow = {
    id: number;
    title: string;
    genre: string;
    duration: number;
    poster: string | null;
    cinema_sessions_count: number;
    can: { delete: boolean };
};

type Paginated = {
    data: FilmRow[];
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    films: Paginated;
    filters: { search: string | null; genre: string | null };
    genreOptions: string[];
}>();

const search = ref(props.filters.search ?? '');
const genre = ref(props.filters.genre ?? '');

const applyFilters = useDebounceFn(() => {
    router.get(
        index.url({
            query: {
                search: search.value || undefined,
                genre: genre.value || undefined,
            },
        }),
        {},
        { preserveState: true, replace: true },
    );
}, 300);

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Films', href: index.url() }],
    },
});
</script>

<template>
    <Head title="Gestion des films" />

    <AdminPage
        title="Gestion des films"
        description="Ajoutez et modifiez la programmation des films."
    >
        <template #actions>
            <Button as-child>
                <Link :href="create.url()">Ajouter un film</Link>
            </Button>
        </template>

        <Card>
            <CardContent class="space-y-4 pt-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="search">Recherche</Label>
                        <Input
                            id="search"
                            v-model="search"
                            placeholder="Titre…"
                            @update:model-value="applyFilters"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="filter-genre">Genre</Label>
                        <select
                            id="filter-genre"
                            v-model="genre"
                            :class="
                                cn(
                                    'h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs',
                                )
                            "
                            @change="applyFilters"
                        >
                            <option value="">Tous les genres</option>
                            <option
                                v-for="g in genreOptions"
                                :key="g"
                                :value="g"
                            >
                                {{ g }}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-md border">
                    <table class="w-full min-w-[720px] text-sm">
                        <thead class="bg-muted/50 text-left">
                            <tr>
                                <th class="px-4 py-3 font-medium">Affiche</th>
                                <th class="px-4 py-3 font-medium">Titre</th>
                                <th class="px-4 py-3 font-medium">Genre</th>
                                <th class="px-4 py-3 font-medium">Durée</th>
                                <th class="px-4 py-3 font-medium">Séances</th>
                                <th class="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="film in films.data"
                                :key="film.id"
                                class="border-t"
                            >
                                <td class="px-4 py-3">
                                    <img
                                        :src="posterSrc(film.poster)"
                                        :alt="film.title"
                                        class="h-16 w-11 rounded object-cover"
                                        @error="handlePosterError"
                                    />
                                </td>
                                <td class="px-4 py-3 font-medium">
                                    {{ film.title }}
                                </td>
                                <td class="px-4 py-3">{{ film.genre }}</td>
                                <td class="px-4 py-3">
                                    {{ formatDuration(film.duration) }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ film.cinema_sessions_count }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <Button
                                            as-child
                                            size="sm"
                                            variant="outline"
                                        >
                                            <Link :href="edit.url(film.id)">
                                                Modifier
                                            </Link>
                                        </Button>
                                        <ConfirmDeleteButton
                                            v-if="film.can.delete"
                                            :destroy-url="destroy.url(film.id)"
                                            description="Le film sera définitivement supprimé."
                                        />
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="films.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-4 py-8 text-center text-muted-foreground"
                                >
                                    Aucun film trouvé.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <nav
                    v-if="films.links.length > 3"
                    class="flex flex-wrap gap-1"
                    aria-label="Pagination"
                >
                    <template v-for="(link, i) in films.links" :key="i">
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
