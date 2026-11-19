<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { selectableRoles } from '@/lib/userRoles';

const props = defineProps<{
    errors: Record<string, string | undefined>;
    canAssignSuperRole: boolean;
    requirePassword: boolean;
    disableRole?: boolean;
    defaults?: {
        name?: string;
        email?: string;
        role?: string;
    };
}>();

const roleOptions = selectableRoles(props.canAssignSuperRole);
</script>

<template>
    <div class="space-y-4">
        <div class="grid gap-2">
            <Label for="name">Nom</Label>
            <Input
                id="name"
                name="name"
                :default-value="defaults?.name"
                required
                autocomplete="name"
            />
            <InputError :message="errors.name" />
        </div>

        <div class="grid gap-2">
            <Label for="email">Adresse e-mail</Label>
            <Input
                id="email"
                type="email"
                name="email"
                :default-value="defaults?.email"
                required
                autocomplete="username"
            />
            <InputError :message="errors.email" />
        </div>

        <div class="grid gap-2">
            <Label for="password">
                Mot de passe
                <span v-if="!requirePassword" class="text-muted-foreground">
                    (facultatif)
                </span>
            </Label>
            <PasswordInput
                id="password"
                name="password"
                :required="requirePassword"
                autocomplete="new-password"
            />
            <InputError :message="errors.password" />
        </div>

        <div class="grid gap-2">
            <Label for="password_confirmation"
                >Confirmation du mot de passe</Label
            >
            <PasswordInput
                id="password_confirmation"
                name="password_confirmation"
                :required="requirePassword"
                autocomplete="new-password"
            />
        </div>

        <div class="grid gap-2">
            <Label for="role">Rôle</Label>
            <select
                id="role"
                name="role"
                :disabled="disableRole"
                required
                :class="
                    cn(
                        'h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-xs',
                        'outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50',
                        disableRole && 'cursor-not-allowed opacity-50',
                    )
                "
            >
                <option
                    v-for="option in roleOptions"
                    :key="option.value"
                    :value="option.value"
                    :selected="
                        (defaults?.role ?? 'spectateur') === option.value
                    "
                >
                    {{ option.label }}
                </option>
            </select>
            <InputError :message="errors.role" />
        </div>
    </div>
</template>
