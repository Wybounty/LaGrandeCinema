<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import AdminPage from '@/components/admin/AdminPage.vue';
import UserFormFields from '@/components/admin/UserFormFields.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Spinner } from '@/components/ui/spinner';
import { edit, index, update } from '@/routes/users';
import type { ManagedUserForm } from '@/types/user';

const props = defineProps<{
    managedUser: ManagedUserForm;
    canAssignSuperRole: boolean;
    isSelf: boolean;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Utilisateurs', href: index.url() },
            {
                title: props.managedUser.name,
                href: edit.url(props.managedUser.id),
            },
        ],
    },
});
</script>

<template>
    <Head :title="`Modifier — ${managedUser.name}`" />

    <AdminPage
        title="Modifier l'utilisateur"
        :description="managedUser.email"
    >
        <Card class="max-w-lg">
            <CardContent class="pt-6">
                <Form
                    v-bind="update.form(managedUser.id)"
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                    disable-while-processing
                >
                    <UserFormFields
                        :errors="errors"
                        :can-assign-super-role="canAssignSuperRole"
                        :require-password="false"
                        :disable-role="isSelf"
                        :defaults="managedUser"
                    />
                    <p v-if="isSelf" class="text-sm text-muted-foreground">
                        Vous ne pouvez pas modifier votre propre rôle.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <Button type="submit" :disabled="processing">
                            <Spinner v-if="processing" class="mr-2" />
                            Enregistrer
                        </Button>
                        <Button as-child variant="outline">
                            <Link :href="index.url()">Retour</Link>
                        </Button>
                    </div>
                </Form>
            </CardContent>
        </Card>
    </AdminPage>
</template>