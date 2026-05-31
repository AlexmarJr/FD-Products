<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="relative min-h-screen overflow-x-hidden overflow-hidden bg-[#08111f] text-white">
        <div class="absolute inset-0 pointer-events-none overflow-hidden">
            <div class="absolute -left-24 top-0 h-80 w-80 rounded-full bg-[#ff8a2a]/20 blur-3xl"></div>
            <div class="absolute right-0 top-24 h-96 w-96 rounded-full bg-[#1f5eff]/20 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
        </div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-7xl flex-col overflow-x-hidden px-6 py-6 lg:px-10">
            <div class="flex items-center justify-between rounded-full border border-white/10 bg-white/5 px-5 py-4 backdrop-blur">
                <Link href="/" class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-[#ff8a2a] to-[#ff5f1f] text-sm font-bold text-white shadow-lg shadow-[#ff8a2a]/30">
                        FD
                    </div>
                    <div>
                        <p class="text-sm font-semibold tracking-[0.28em] text-white/55 uppercase">FirstDecision</p>
                        <p class="text-xs text-white/50">Plataforma de produtos e decisão</p>
                    </div>
                </Link>
            </div>

            <div class="flex flex-1 items-center justify-center py-10">
                <slot />
            </div>
        </div>
    </div>
</template>
