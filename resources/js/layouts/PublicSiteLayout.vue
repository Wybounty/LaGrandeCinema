<script setup lang="ts">
import SiteFooter from '@/components/site/SiteFooter.vue';
import SiteHeader from '@/components/site/SiteHeader.vue';
import { Toaster } from '@/components/ui/sonner';
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <SiteHeader />
        <main class="flex flex-1 flex-col">
            <slot />
        </main>
        <SiteFooter />
        <Toaster />
    </div>
</template>