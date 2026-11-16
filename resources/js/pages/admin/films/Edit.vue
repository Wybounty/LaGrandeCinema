<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AdminPage from '@/components/admin/AdminPage.vue';
import FilmFormFields from '@/components/admin/FilmFormFields.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { index, update } from '@/routes/admin/films';

defineProps<{
    film: {
        id: number;
        title: string;
        synopsis: string;
        duration: number;
        genre: string;
        poster: string | null;
    };
    genreOptions: string[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Films', href: index.url() },
            { title: 'Modifier' },
        ],
    },
});
</script>

<template>
    <Head :title="`Modifier — ${film.title}`" />

    <AdminPage title="Modifier le film" :description="film.title">
        <Card class="max-w-xl">
            <CardContent class="pt-6">
                <Form
                    v-bind="update.form(film.id)"
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                    disable-while-processing
                    enctype="multipart/form-data"
                >
                    <FilmFormFields
                        :errors="errors"
                        :genre-options="genreOptions"
                        :require-poster="false"
                        :current-poster="film.poster"
                        :defaults="film"
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