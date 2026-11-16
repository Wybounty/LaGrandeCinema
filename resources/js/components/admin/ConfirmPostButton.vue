<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';

defineProps<{
    actionUrl: string;
    label?: string;
    description?: string;
    submitLabel?: string;
    variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
}>();
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button
                type="button"
                :variant="variant ?? 'destructive'"
                size="sm"
            >
                {{ label ?? 'Confirmer' }}
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Confirmer l'action</DialogTitle>
                <DialogDescription>
                    {{ description ?? 'Cette action est irréversible.' }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2 sm:gap-0">
                <DialogClose as-child>
                    <Button type="button" variant="outline">Retour</Button>
                </DialogClose>
                <Form
                    :action="actionUrl"
                    method="post"
                    class="inline"
                    disable-while-processing
                >
                    <Button type="submit" variant="destructive">
                        {{ submitLabel ?? 'Confirmer' }}
                    </Button>
                </Form>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>