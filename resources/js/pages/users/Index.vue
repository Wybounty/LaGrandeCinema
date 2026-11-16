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
import { roleLabels } from '@/lib/userRoles';
import { cn } from '@/lib/utils';
import { create, edit, index, destroy } from '@/routes/users';
import type { ManagedUserRow } from '@/types/user';

type PaginatedUsers = {
    data: ManagedUserRow[];
    links: { url: string | null; label: string; active: boolean }[];
};

const props = defineProps<{
    users: PaginatedUsers;
    filters: { search: string | null; role: string | null };
    canAssignSuperRole: boolean;
}>();

const search = ref(props.filters.search ?? '');
const role = ref(props.filters.role ?? '');

const applyFilters = useDebounceFn(() => {
    router.get(
        index.url({
            query: {
                search: search.value || undefined,
                role: role.value || undefined,
            },
        }),
        {},
        { preserveState: true, replace: true },
    );
}, 300);

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Utilisateurs', href: index.url() },
        ],
    },
});
</script>

<template>
    <Head title="Utilisateurs" />

    <AdminPage
        title="Utilisateurs"
        description="Gérez les comptes et les rôles du cinéma."
    >
        <template #actions>
            <Button as-child>
                <Link :href="create.url()">Ajouter un utilisateur</Link>
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
                            placeholder="Nom ou e-mail…"
                            @update:model-value="applyFilters"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="filter-role">Rôle</Label>
                        <select
                            id="filter-role"
                            v-model="role"
                            @change="applyFilters"
                            :class="
                                cn(
                                    'border-input bg-background h-9 w-full rounded-md border px-3 py-1 text-sm shadow-xs',
                                )
                            "
                        >
                            <option value="">Tous les rôles</option>
                            <option value="spectateur">Spectateur</option>
                            <option value="administrateur">Administrateur</option>
                            <option
                                v-if="canAssignSuperRole"
                                value="super_administrateur"
                            >
                                Super administrateur
                            </option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-md border">
                    <table class="w-full min-w-[640px] text-sm">
                        <thead class="bg-muted/50 text-left">
                            <tr>
                                <th class="px-4 py-3 font-medium">Nom</th>
                                <th class="px-4 py-3 font-medium">E-mail</th>
                                <th class="px-4 py-3 font-medium">Rôle</th>
                                <th class="px-4 py-3 font-medium">Créé le</th>
                                <th class="px-4 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="border-t"
                            >
                                <td class="px-4 py-3">{{ user.name }}</td>
                                <td class="px-4 py-3">{{ user.email }}</td>
                                <td class="px-4 py-3">
                                    {{ roleLabels[user.role] ?? user.role }}
                                </td>
                                <td class="px-4 py-3">
                                    {{
                                        new Date(user.created_at).toLocaleString(
                                            'fr-FR',
                                        )
                                    }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <Button
                                            v-if="user.can.update"
                                            as-child
                                            size="sm"
                                            variant="outline"
                                        >
                                            <Link :href="edit.url(user.id)"
                                                >Modifier</Link
                                            >
                                        </Button>
                                        <ConfirmDeleteButton
                                            v-if="user.can.delete"
                                            :destroy-url="destroy.url(user.id)"
                                        />
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td
                                    colspan="5"
                                    class="px-4 py-8 text-center text-muted-foreground"
                                >
                                    Aucun utilisateur trouvé.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <nav
                    v-if="users.links.length > 3"
                    class="flex flex-wrap gap-1"
                    aria-label="Pagination"
                >
                    <template v-for="(link, i) in users.links" :key="i">
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