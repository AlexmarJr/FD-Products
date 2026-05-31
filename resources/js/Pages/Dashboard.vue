<script setup>
import DashboardSidebar from '@/Components/DashboardSidebar.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const stats = ref({
    total_products: 0,
    status_counts: {},
    total_cost_value: 0,
    total_sale_value: 0,
    estimated_profit: 0,
    top_suppliers: [],
});
const loading = ref(true);

function formattedCurrency(value) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
}

function statusClasses(status) {
    const normalized = String(status || '').toLowerCase();

    if (normalized === 'ativo') {
        return {
            card: 'border-emerald-300/30 bg-emerald-500/10',
            label: 'text-emerald-200',
            badge: 'bg-emerald-400/20 text-emerald-100',
        };
    }

    if (normalized === 'pausado') {
        return {
            card: 'border-amber-300/30 bg-amber-500/10',
            label: 'text-amber-200',
            badge: 'bg-amber-400/20 text-amber-100',
        };
    }

    return {
        card: 'border-white/10 bg-white/5',
        label: 'text-white/80',
        badge: 'bg-white/10 text-white',
    };
}

async function fetchStats() {
    try {
        const response = await axios.get('/dashboard/stats');
        stats.value = response.data;
    } catch (error) {
        console.error('Erro ao carregar dados do dashboard', error);
    }
    finally {
        loading.value = false;
    }
}

onMounted(() => {
    fetchStats();
});
</script>

<template>

    <AuthenticatedLayout>
        <div class="relative mx-auto flex min-h-[calc(100vh-9rem)] w-full max-w-[88rem] gap-6 overflow-x-hidden px-4 py-6 sm:px-6 lg:px-8">
            <DashboardSidebar />

            <div class="min-w-0 flex-1">
                <div class="flex h-full min-h-[calc(100vh-9rem)] flex-col rounded-[2rem] border border-white/10 bg-gradient-to-br from-[#0f1f35]/90 to-[#0a1527]/90 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.22)] backdrop-blur overflow-hidden">
                    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <h2 class="text-3xl font-bold tracking-tight text-white">Visão geral</h2>
                            <p class="mt-1 text-sm text-white/60">Resumo dos produtos cadastrados e resultados estimados.</p>
                        </div>
                        <div class="rounded-xl border border-white/15 bg-white/5 px-4 py-2 text-sm text-white/80">
                            Total de produtos: <span class="font-semibold text-white">{{ stats.total_products || 0 }}</span>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <template v-if="loading">
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-5 animate-pulse">
                                <div class="h-5 w-24 bg-white/10 rounded mb-4"></div>
                                <div class="h-9 w-20 bg-white/10 rounded"></div>
                            </div>
                            <div class="rounded-2xl border border-red-300/20 bg-red-500/10 p-5 animate-pulse">
                                <div class="h-4 w-28 bg-white/10 rounded mb-3"></div>
                                <div class="h-7 w-32 bg-white/10 rounded"></div>
                            </div>
                            <div class="rounded-2xl border border-emerald-300/20 bg-emerald-500/10 p-5 animate-pulse">
                                <div class="h-4 w-28 bg-white/10 rounded mb-3"></div>
                                <div class="h-7 w-32 bg-white/10 rounded"></div>
                            </div>
                            <div class="rounded-2xl border p-5 animate-pulse">
                                <div class="h-4 w-24 bg-white/10 rounded mb-3"></div>
                                <div class="h-7 w-32 bg-white/10 rounded"></div>
                            </div>
                        </template>
                        <template v-else>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-5">
                            <p class="text-xs uppercase tracking-[0.16em] text-white/50">Produtos</p>
                            <p class="mt-3 text-3xl font-bold text-white">{{ stats.total_products || 0 }}</p>
                        </div>

                        <div class="rounded-2xl border border-red-300/20 bg-red-500/10 p-5">
                            <p class="text-xs uppercase tracking-[0.16em] text-red-200/70">Custo total</p>
                            <p class="mt-3 text-2xl font-bold text-red-200">{{ formattedCurrency(stats.total_cost_value || 0) }}</p>
                        </div>

                        <div class="rounded-2xl border border-emerald-300/20 bg-emerald-500/10 p-5">
                            <p class="text-xs uppercase tracking-[0.16em] text-emerald-200/70">Venda total</p>
                            <p class="mt-3 text-2xl font-bold text-emerald-200">{{ formattedCurrency(stats.total_sale_value || 0) }}</p>
                        </div>

                        <div class="rounded-2xl border p-5" :class="stats.estimated_profit >= 0 ? 'border-emerald-300/30 bg-emerald-500/10' : 'border-red-300/30 bg-red-500/10'">
                            <p class="text-xs uppercase tracking-[0.16em]" :class="stats.estimated_profit >= 0 ? 'text-emerald-200/70' : 'text-red-200/70'">Lucro estimado</p>
                            <p class="mt-3 text-2xl font-bold" :class="stats.estimated_profit >= 0 ? 'text-emerald-200' : 'text-red-200'">
                                {{ formattedCurrency(stats.estimated_profit || 0) }}
                            </p>
                        </div>
                        </template>
                    </div>

                    <div class="mt-8 rounded-2xl border border-white/10 bg-white/[0.03] p-5">
                        <h3 class="mb-4 text-lg font-semibold text-white">Produtos por status</h3>
                        <div v-if="Object.keys(stats.status_counts || {}).length" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            <div
                                v-for="(count, status) in stats.status_counts"
                                :key="status"
                                class="flex items-center justify-between rounded-xl border px-4 py-3"
                                :class="statusClasses(status).card"
                            >
                                <span class="text-sm font-medium capitalize" :class="statusClasses(status).label">{{ status }}</span>
                                <span class="rounded-lg px-2.5 py-1 text-sm font-bold" :class="statusClasses(status).badge">{{ count }}</span>
                            </div>
                        </div>
                        <p v-else class="text-sm text-white/50">Nenhum status encontrado.</p>
                    </div>

                    <div class="mt-8 flex min-h-0 flex-1 flex-col rounded-2xl border border-white/10 bg-white/[0.03] p-5">
                        <h3 class="mb-4 text-lg font-semibold text-white">Principais fornecedores</h3>
                        <div v-if="loading" class="h-40 grid grid-cols-3 gap-2">
                            <div v-for="n in 9" :key="n" class="rounded-lg border border-white/10 bg-white/5 px-2 py-2 animate-pulse"></div>
                        </div>
                        <div v-else class="h-full min-h-0 overflow-y-auto pr-1">
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                                <div
                                    v-for="supplier in stats.top_suppliers"
                                    :key="supplier.name"
                                    class="rounded-lg border border-white/10 bg-white/5 px-2.5 py-2"
                                >
                                    <p class="truncate text-[11px] font-medium text-white/85">{{ supplier.name }}</p>
                                    <p class="mt-1 text-[11px] font-bold text-[#8fb0ff]">{{ supplier.count }} produto(s)</p>
                                </div>
                            </div>
                        </div>
                        <p v-if="!loading && !(stats.top_suppliers || []).length" class="text-sm text-white/50">Nenhum fornecedor encontrado.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
