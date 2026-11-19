<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AdminPage from '@/components/admin/AdminPage.vue';
import UserFormFields from '@/components/admin/UserFormFields.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { create, index, store } from '@/routes/users';

defineProps<{
    canAssignSuperRole: boolean;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Utilisateurs', href: index.url() },
            { title: 'Ajouter', href: create.url() },
        ],
    },
});
</script>

<template>
    <Head title="Ajouter un utilisateur" />

    <AdminPage
        title="Ajouter un utilisateur"
        description="Créez un compte avec un rôle adapté."
    >
        <Card class="max-w-lg">
            <CardContent class="pt-6">
                <Form
                    v-bind="store.form()"
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                    disable-while-processing
                >
                    <UserFormFields
                        :errors="errors"
                        :can-assign-super-role="canAssignSuperRole"
                        :require-password="true"
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
