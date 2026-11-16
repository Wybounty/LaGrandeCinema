<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Calendar,
    Clapperboard,
    ClipboardList,
    DoorOpen,
    FolderGit2,
    LayoutGrid,
    Ticket,
    Users,
} from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as ticketsIndex } from '@/routes/tickets';
import { index as adminFilmsIndex } from '@/routes/admin/films';
import { index as adminReservationsIndex } from '@/routes/admin/reservations';
import { index as adminRoomsIndex } from '@/routes/admin/rooms';
import { index as adminSessionsIndex } from '@/routes/admin/sessions';
import { index as usersIndex } from '@/routes/users';
import type { Auth, NavItem } from '@/types';

const page = usePage<{ auth: Auth; canManageUsers: boolean }>();

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];

    if (page.props.auth.user?.role === 'spectateur') {
        items.push({
            title: 'Mes billets',
            href: ticketsIndex.url(),
            icon: Ticket,
        });
    }

    if (page.props.canManageUsers) {
        items.push(
            {
                title: 'Films',
                href: adminFilmsIndex.url(),
                icon: Clapperboard,
            },
            {
                title: 'Séances',
                href: adminSessionsIndex.url(),
                icon: Calendar,
            },
            {
                title: 'Salles',
                href: adminRoomsIndex.url(),
                icon: DoorOpen,
            },
            {
                title: 'Réservations',
                href: adminReservationsIndex.url(),
                icon: ClipboardList,
            },
            {
                title: 'Utilisateurs',
                href: usersIndex.url(),
                icon: Users,
            },
        );
    }

    return items;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
