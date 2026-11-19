<script setup lang="ts">
import {
    BarChart3,
    CalendarDays,
    Clapperboard,
    Film,
    LineChart,
    Music2,
    QrCode,
    ScanLine,
    Ticket,
    Users,
} from '@lucide/vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
</script>

<template>
    <section class="space-y-10" aria-labelledby="cinema-presentation">
        <div
            class="grid gap-3 sm:grid-cols-3"
            aria-label="Chiffres clés du cinéma"
        >
            <Card class="border-primary/20 bg-primary/5 shadow-none">
                <CardContent class="px-4 py-5 text-center">
                    <p class="text-3xl font-semibold tabular-nums">50+</p>
                    <p class="text-sm text-muted-foreground">ans d'histoire</p>
                </CardContent>
            </Card>
            <Card class="border-primary/20 bg-primary/5 shadow-none">
                <CardContent class="px-4 py-5 text-center">
                    <p class="text-3xl font-semibold tabular-nums">10</p>
                    <p class="text-sm text-muted-foreground">
                        salles modulables
                    </p>
                </CardContent>
            </Card>
            <Card class="border-primary/20 bg-primary/5 shadow-none">
                <CardContent class="px-4 py-5 text-center">
                    <p class="text-3xl font-semibold tabular-nums">1 700</p>
                    <p class="text-sm text-muted-foreground">places au total</p>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle id="cinema-presentation" class="text-lg">
                        Un lieu culturel ancré dans la ville
                    </CardTitle>
                    <CardDescription>
                        Le Grand Cinéma accompagne la vie culturelle locale
                        depuis plus de cinquante ans.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4 text-sm text-muted-foreground">
                    <p>
                        La programmation mêle films grand public, cinéma
                        d'auteur, événements culturels et projections
                        spéciales. Le cinéma accueille les habitants, les
                        touristes, les professionnels et les publics des
                        manifestations culturelles.
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">
                        Vers la réservation en ligne
                    </CardTitle>
                    <CardDescription>
                        Moderniser l'expérience pour répondre à une demande
                        croissante.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4 text-sm text-muted-foreground">
                    <p>
                        Jusqu'à présent, les réservations passaient
                        uniquement par le guichet, avec des files d'attente
                        importantes, parfois plus de trente minutes, et des
                        spectateurs qui renonçaient à leur séance.
                    </p>
                    <p>
                        L'objectif du projet est d'accroître la fréquentation
                        de 30&nbsp;% sur douze mois, tout en réduisant
                        l'attente, en améliorant le parcours client, en
                        optimisant le remplissage des salles et en facilitant
                        l'accès aux voyageurs habitués aux services numériques.
                    </p>
                </CardContent>
            </Card>
        </div>

        <Card class="border-amber-500/30 bg-amber-500/5">
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-lg">
                    <Music2 class="size-5 text-amber-700 dark:text-amber-400" />
                    Festival de Jazz
                </CardTitle>
                <CardDescription>
                    Une période clé pour l'activité du cinéma.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-3 text-sm text-muted-foreground">
                <p>
                    Chaque année, le festival attire des milliers de visiteurs
                    du monde entier. La fréquentation du cinéma y augmente, avec
                    des réservations concentrées sur certaines plages horaires.
                </p>
                <p>
                    Cette plateforme est conçue pour absorber ces pics
                    d'affluence et offrir un parcours de réservation fluide,
                    y compris pendant les événements à forte affluence.
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-lg">
                    <LineChart class="size-5" />
                    Fréquentation — données de référence
                </CardTitle>
                <CardDescription>
                    Informations issues du cahier des charges, hors statistiques
                    temps réel de l'application.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <ul
                    class="grid gap-4 text-sm sm:grid-cols-3"
                    role="list"
                >
                    <li class="rounded-lg border border-border p-4">
                        <p class="font-medium">En semaine</p>
                        <p class="mt-1 text-muted-foreground">
                            Entre 800 et 1 200 spectateurs par jour.
                        </p>
                    </li>
                    <li class="rounded-lg border border-border p-4">
                        <p class="font-medium">Le week-end</p>
                        <p class="mt-1 text-muted-foreground">
                            Entre 1 200 et 1 600 spectateurs par jour.
                        </p>
                    </li>
                    <li class="rounded-lg border border-border p-4">
                        <p class="font-medium">Festival de Jazz</p>
                        <p class="mt-1 text-muted-foreground">
                            Hausse de fréquentation et réservations groupées
                            sur des créneaux précis.
                        </p>
                    </li>
                </ul>
            </CardContent>
        </Card>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Pour les spectateurs</CardTitle>
                    <CardDescription>
                        Services disponibles sur ce site.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <ul class="space-y-2.5 text-sm text-muted-foreground">
                        <li class="flex gap-2">
                            <Film class="mt-0.5 size-4 shrink-0" />
                            Consulter les films et les horaires des séances.
                        </li>
                        <li class="flex gap-2">
                            <Ticket class="mt-0.5 size-4 shrink-0" />
                            Visualiser les places disponibles et réserver en
                            ligne.
                        </li>
                        <li class="flex gap-2">
                            <CalendarDays class="mt-0.5 size-4 shrink-0" />
                            Payer en ligne via Stripe une fois le récapitulatif
                            validé.
                        </li>
                        <li class="flex gap-2">
                            <QrCode class="mt-0.5 size-4 shrink-0" />
                            Recevoir un billet dématérialisé avec QR code.
                        </li>
                        <li class="flex gap-2">
                            <Users class="mt-0.5 size-4 shrink-0" />
                            Retrouver ses billets dans l'espace personnel.
                        </li>
                    </ul>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Pour l'administration</CardTitle>
                    <CardDescription>
                        Outils de gestion accessibles depuis le tableau de
                        bord.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <ul class="space-y-2.5 text-sm text-muted-foreground">
                        <li class="flex gap-2">
                            <Clapperboard class="mt-0.5 size-4 shrink-0" />
                            Gestion des films, des salles et des séances.
                        </li>
                        <li class="flex gap-2">
                            <Ticket class="mt-0.5 size-4 shrink-0" />
                            Gestion des réservations et des utilisateurs.
                        </li>
                        <li class="flex gap-2">
                            <BarChart3 class="mt-0.5 size-4 shrink-0" />
                            Consultation des statistiques d'activité.
                        </li>
                        <li class="flex gap-2">
                            <ScanLine class="mt-0.5 size-4 shrink-0" />
                            Contrôle des billets à l'entrée.
                        </li>
                    </ul>
                </CardContent>
            </Card>
        </div>
    </section>
</template>