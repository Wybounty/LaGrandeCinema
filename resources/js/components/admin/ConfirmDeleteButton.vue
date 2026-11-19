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
    destroyUrl: string;
    label?: string;
    description?: string;
}>();
</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button type="button" variant="destructive" size="sm">
                {{ label ?? 'Supprimer' }}
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>Confirmer la suppression</DialogTitle>
                <DialogDescription>
                    {{ description ?? 'Cette action est irréversible.' }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2 sm:gap-0">
                <DialogClose as-child>
                    <Button type="button" variant="outline">Annuler</Button>
                </DialogClose>
                <Form
                    :action="destroyUrl"
                    method="delete"
                    class="inline"
                    disable-while-processing
                >
                    <Button type="submit" variant="destructive">
                        Supprimer
                    </Button>
                </Form>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
