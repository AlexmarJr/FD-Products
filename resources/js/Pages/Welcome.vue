<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const productCards = [
    {
        title: 'Catálogo vivo',
        description:
            'Visualize linhas, status e prioridades em um ambiente pensado para operação comercial.',
    },
    {
        title: 'Decisão rápida',
        description:
            'Centralize sinais do negócio e acompanhe o que precisa de ação com menos fricção.',
    },
    {
        title: 'Visão de time',
        description:
            'Conecte produto, vendas e operação em uma experiência consistente para o dia a dia.',
    },
];

const metrics = [
    { value: '12x', label: 'mais clareza no acompanhamento' },
    { value: '1 fluxo', label: 'para produto e operação' },
    { value: '100%', label: 'foco em decisão prática' },
];
</script>

<template>
    <Head title="FirstDecision" />

    <div class="min-h-screen bg-[#08111f] text-white">
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -left-24 top-0 h-80 w-80 rounded-full bg-[#ff8a2a]/20 blur-3xl"></div>
            <div class="absolute right-0 top-24 h-96 w-96 rounded-full bg-[#1f5eff]/20 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
        </div>

        <div class="relative mx-auto flex min-h-screen w-full max-w-7xl flex-col px-6 py-6 lg:px-10">
            <header class="flex items-center justify-between rounded-full border border-white/10 bg-white/5 px-5 py-4 backdrop-blur">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-[#ff8a2a] to-[#ff5f1f] text-sm font-bold text-white shadow-lg shadow-[#ff8a2a]/30">
                        FD
                    </div>
                    <div>
                        <p class="text-sm font-semibold tracking-[0.28em] text-white/55 uppercase">FirstDecision</p>
                        <p class="text-xs text-white/50">Plataforma de produtos e decisão</p>
                    </div>
                </div>

                <nav v-if="canLogin" class="flex items-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/15"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-medium text-white transition hover:bg-white/15"
                        >
                            Entrar
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-full bg-[#ff8a2a] px-4 py-2 text-sm font-semibold text-[#08111f] transition hover:bg-[#ffa24f]"
                        >
                            Criar conta
                        </Link>
                    </template>
                </nav>
            </header>

            <main class="grid flex-1 items-center gap-8 py-10 lg:grid-cols-[1.05fr_0.95fr] lg:gap-12">
                <section class="space-y-8">
                    <div class="inline-flex items-center rounded-full border border-[#ff8a2a]/20 bg-[#ff8a2a]/10 px-4 py-2 text-sm font-medium text-[#ffb777]">
                        Estilo FirstDecision para produtos e operação
                    </div>

                    <div class="space-y-6">
                        <p class="text-sm font-semibold uppercase tracking-[0.32em] text-[#8fb0ff]">
                            Produtos, visão e ação
                        </p>
                        <h1 class="max-w-3xl text-5xl font-semibold leading-tight text-balance sm:text-6xl">
                            Um espaço para enxergar produto, priorizar e decidir melhor.
                        </h1>
                        <p class="max-w-2xl text-lg leading-8 text-white/70">
                            A FirstDecision concentra a rotina em um ambiente claro, rápido e pensado para times que precisam acompanhar produtos sem perder contexto.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-4">
                        <Link
                            :href="route('login')"
                            class="rounded-full bg-white px-6 py-3 text-sm font-semibold text-[#08111f] transition hover:bg-[#f1f5f9]"
                        >
                            Entrar
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-full border border-white/15 bg-white/5 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/10"
                        >
                            Criar conta
                        </Link>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-3">
                        <article
                            v-for="metric in metrics"
                            :key="metric.label"
                            class="rounded-3xl border border-white/10 bg-white/5 p-5 backdrop-blur"
                        >
                            <p class="text-3xl font-semibold text-white">{{ metric.value }}</p>
                            <p class="mt-2 text-sm leading-6 text-white/65">{{ metric.label }}</p>
                        </article>
                    </div>
                </section>

                <section class="relative">
                    <div class="absolute inset-0 -z-10 rounded-[2rem] bg-gradient-to-br from-[#ff8a2a]/30 via-white/5 to-[#1f5eff]/20 blur-2xl"></div>
                    <div class="rounded-[2rem] border border-white/10 bg-white/8 p-6 shadow-[0_30px_80px_rgba(0,0,0,0.35)] backdrop-blur">

                        <div class="grid gap-4">
                            <article
                                v-for="card in productCards"
                                :key="card.title"
                                class="rounded-2xl border border-white/10 bg-[#0f1b31] p-5 transition duration-300 hover:-translate-y-0.5 hover:border-[#ff8a2a]/40"
                            >
                                <div class="mb-4 h-1.5 w-16 rounded-full bg-gradient-to-r from-[#ff8a2a] to-[#ffb777]"></div>
                                <h2 class="text-lg font-semibold text-white">{{ card.title }}</h2>
                                <p class="mt-2 text-sm leading-6 text-white/65">{{ card.description }}</p>
                            </article>
                        </div>

                        <div class="mt-6 rounded-2xl border border-dashed border-white/15 bg-white/5 p-5">
                            <p class="text-sm font-medium uppercase tracking-[0.24em] text-white/45">Acesso rápido</p>
                            <p class="mt-2 text-base leading-7 text-white/70">
                                Entre para acompanhar produtos, revisar prioridades e manter a operação alinhada ao que a empresa precisa entregar.
                            </p>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>
