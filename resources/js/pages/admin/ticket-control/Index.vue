<script setup lang="ts">
import { Head, useHttp } from '@inertiajs/vue3';
import { Html5Qrcode } from 'html5-qrcode';
import { computed, onBeforeUnmount, ref } from 'vue';
import AdminPage from '@/components/admin/AdminPage.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    formatSessionDate,
    formatSessionTime,
} from '@/lib/formatDuration';
import { cn } from '@/lib/utils';
import { index, markUsed, verify } from '@/routes/admin/ticket-control';

type TicketDetails = {
    id: number;
    qr_code: string;
    place_number: number;
    status: string;
    film_title: string;
    session_starts_at: string;
    session_ends_at: string;
    room_name: string;
};

type CheckOutcome = 'valid' | 'used' | 'cancelled' | 'invalid';

type CheckResponse = {
    outcome: CheckOutcome;
    message: string;
    ticket: TicketDetails | null;
    success?: boolean;
};

const http = useHttp({ qr_code: '' });

const manualCode = ref('');
const scanning = ref(false);
const scanError = ref<string | null>(null);
const busy = ref(false);
const lastResponse = ref<CheckResponse | null>(null);

let scanner: Html5Qrcode | null = null;
const scannerElementId = 'ticket-control-qr-reader';

const resultTone = computed(() => {
    if (!lastResponse.value) {
        return null;
    }

    if (lastResponse.value.success) {
        return 'valid';
    }

    switch (lastResponse.value.outcome) {
        case 'valid':
            return 'valid';
        case 'used':
            return 'used';
        default:
            return 'rejected';
    }
});

const canConfirmEntry = computed(
    () => lastResponse.value?.outcome === 'valid' && !busy.value,
);

async function runVerify(qrCode: string): Promise<void> {
    const code = qrCode.trim();
    if (!code) {
        return;
    }

    busy.value = true;
    scanError.value = null;

    try {
        http.qr_code = code;
        const response = (await http.post(verify.url())) as CheckResponse;

        lastResponse.value = response;
        manualCode.value = code;
    } catch {
        scanError.value = 'Impossible de vérifier le billet. Réessayez.';
        lastResponse.value = null;
    } finally {
        busy.value = false;
    }
}

async function confirmEntry(): Promise<void> {
    if (!lastResponse.value?.ticket) {
        return;
    }

    busy.value = true;
    scanError.value = null;

    try {
        http.qr_code = lastResponse.value.ticket.qr_code;
        const response = (await http.post(markUsed.url())) as CheckResponse;

        lastResponse.value = response;
    } catch {
        scanError.value = 'Impossible de confirmer l\'entrée. Réessayez.';
    } finally {
        busy.value = false;
    }
}

async function startScan(): Promise<void> {
    scanError.value = null;
    lastResponse.value = null;

    if (scanner) {
        return;
    }

    scanner = new Html5Qrcode(scannerElementId);

    try {
        await scanner.start(
            { facingMode: 'environment' },
            {
                fps: 10,
                qrbox: { width: 250, height: 250 },
            },
            (decoded) => {
                void stopScan();
                void runVerify(decoded);
            },
            () => {
                // Ignore scan attempts without a readable code.
            },
        );
        scanning.value = true;
    } catch {
        scanError.value =
            'Accès à la caméra refusé ou indisponible. Utilisez la saisie manuelle.';
        scanner = null;
        scanning.value = false;
    }
}

async function stopScan(): Promise<void> {
    if (!scanner) {
        scanning.value = false;
        return;
    }

    try {
        await scanner.stop();
        await scanner.clear();
    } catch {
        // Scanner may already be stopped.
    }

    scanner = null;
    scanning.value = false;
}

onBeforeUnmount(() => {
    void stopScan();
});

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Contrôle des billets', href: index.url() }],
    },
});
</script>

<template>
    <Head title="Contrôle des billets" />

    <AdminPage
        title="Contrôle des billets"
        description="Scannez le QR code ou saisissez l'identifiant du billet, puis confirmez l'entrée si le billet est valide."
    >
        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Scanner</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div
                        :id="scannerElementId"
                        class="min-h-[280px] overflow-hidden rounded-lg border bg-muted/30"
                    />

                    <div class="flex flex-wrap gap-2">
                        <Button
                            type="button"
                            :disabled="scanning || busy"
                            @click="startScan"
                        >
                            Démarrer le scan
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="!scanning || busy"
                            @click="stopScan"
                        >
                            Arrêter le scan
                        </Button>
                    </div>

                    <p
                        v-if="scanError"
                        class="text-sm text-destructive"
                        role="alert"
                    >
                        {{ scanError }}
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Saisie manuelle</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="manual-qr">Identifiant du billet</Label>
                        <Input
                            id="manual-qr"
                            v-model="manualCode"
                            autocomplete="off"
                            placeholder="UUID affiché sous le QR code"
                            @keyup.enter="runVerify(manualCode)"
                        />
                    </div>
                    <Button
                        type="button"
                        :disabled="busy || !manualCode.trim()"
                        @click="runVerify(manualCode)"
                    >
                        Vérifier le billet
                    </Button>
                </CardContent>
            </Card>
        </div>

        <Card
            v-if="lastResponse"
            :class="
                cn(
                    'border-2',
                    resultTone === 'valid' &&
                        'border-emerald-500/60 bg-emerald-500/5',
                    resultTone === 'used' && 'border-amber-500/60 bg-amber-500/5',
                    resultTone === 'rejected' &&
                        'border-destructive/60 bg-destructive/5',
                )
            "
        >
            <CardHeader>
                <CardTitle>
                    <span v-if="lastResponse.outcome === 'valid'"
                        >Billet valide</span
                    >
                    <span v-else-if="lastResponse.outcome === 'used'"
                        >Billet déjà utilisé</span
                    >
                    <span v-else-if="lastResponse.outcome === 'cancelled'"
                        >Billet annulé</span
                    >
                    <span v-else>Billet invalide</span>
                </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <p class="text-sm">{{ lastResponse.message }}</p>

                <dl
                    v-if="lastResponse.ticket"
                    class="grid gap-2 text-sm sm:grid-cols-2"
                >
                    <div>
                        <dt class="text-muted-foreground">Film</dt>
                        <dd class="font-medium">
                            {{ lastResponse.ticket.film_title }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Salle</dt>
                        <dd class="font-medium">
                            {{ lastResponse.ticket.room_name }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Séance</dt>
                        <dd class="font-medium">
                            {{
                                formatSessionDate(
                                    lastResponse.ticket.session_starts_at,
                                )
                            }}
                            à
                            {{
                                formatSessionTime(
                                    lastResponse.ticket.session_starts_at,
                                )
                            }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Identifiant</dt>
                        <dd class="break-all font-mono text-xs">
                            {{ lastResponse.ticket.qr_code }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted-foreground">Place</dt>
                        <dd class="font-medium">
                            n° {{ lastResponse.ticket.place_number }}
                        </dd>
                    </div>
                </dl>

                <Button
                    v-if="canConfirmEntry"
                    type="button"
                    class="bg-emerald-600 hover:bg-emerald-700"
                    :disabled="busy"
                    @click="confirmEntry"
                >
                    Confirmer l'entrée
                </Button>
            </CardContent>
        </Card>
    </AdminPage>
</template>