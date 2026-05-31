<script setup>
import { Link, usePage } from '@inertiajs/vue3';
</script>

<template>
    <aside class="hidden w-72 shrink-0 self-stretch lg:block">
        <div class="flex h-full flex-col rounded-[1.75rem] border border-white/10 bg-[#0b1526]/95 p-4 shadow-[0_24px_60px_rgba(0,0,0,0.28)] backdrop-blur">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-[#ff8a2a] to-[#ff5f1f] text-sm font-bold text-white shadow-lg shadow-[#ff8a2a]/30">
                    FD
                </div>
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-white">FirstDecision</p>
                    <p class="mt-1 text-xs text-white/45">Painel interno</p>
                </div>
            </div>

            <hr class="my-4 border-white/10" />
            <div class="space-y-2">
                <Link
                    :href="route('dashboard')"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition"
                    :class="route().current('dashboard') ? 'border border-[#ff8a2a]/20 bg-[#ff8a2a]/10 text-[#ffb777]' : 'border border-white/10 bg-white/5 text-white/70 hover:border-white/20 hover:bg-white/10 hover:text-white'"
                >
                    <span class="h-2.5 w-2.5 rounded-full" :class="route().current('dashboard') ? 'bg-[#ff8a2a]' : 'bg-white/30'"></span>
                    Início
                </Link>
                <Link
                    :href="route('products.index')"
                    class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium transition"
                    :class="route().current('products.index') ? 'border border-[#ff8a2a]/20 bg-[#ff8a2a]/10 text-[#ffb777]' : 'border border-white/10 bg-white/5 text-white/70 hover:border-white/20 hover:bg-white/10 hover:text-white'"
                >
                    <span class="h-2.5 w-2.5 rounded-full" :class="route().current('products.index') ? 'bg-[#ff8a2a]' : 'bg-white/30'"></span>
                    Produtos
                </Link>
            </div>
        </div>
    </aside>
</template>