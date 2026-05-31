<script setup>
import DashboardSidebar from '@/Components/DashboardSidebar.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed, ref, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    products: {
        type: [Array, Object],
        default: () => [],
    },
    cost_price_filter: {
        type: Array,
        default: () => [],
    },
    sale_price_filter: {
        type: Array,
        default: () => [],
    },
});

// ─── Busca ─────────────────────────────────────────────────────────────────

const search = ref(new URLSearchParams(window.location.search).get('q') || '');
const selectedCostPrices = ref(new URLSearchParams(window.location.search).getAll('cost_price_filter'));
const selectedSalePrices = ref(new URLSearchParams(window.location.search).getAll('sale_price_filter'));
const showCostDropdown = ref(false);
const showSaleDropdown = ref(false);
let searchTimeout = null;

watch([search, selectedCostPrices, selectedSalePrices], ([q]) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const params = {
            ...(q.trim() ? { q: q.trim() } : {}),
            ...(selectedCostPrices.value.length ? { cost_price_filter: selectedCostPrices.value } : {}),
            ...(selectedSalePrices.value.length ? { sale_price_filter: selectedSalePrices.value } : {}),
            page: 1,
        };
        router.get(
            route('products.index'),
            params,
            { preserveState: true, preserveScroll: true, replace: true }
        );
    }, 1000);
}, { deep: true });

// ─── Ordenação ─────────────────────────────────────────────────────────────

const sortKey = ref('name');
const sortDirection = ref('asc');

const toggleSort = (key) => {
    if (sortKey.value === key) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortDirection.value = 'asc';
    }
};

// ─── Dados dos produtos ────────────────────────────────────────────────────

const productsList = computed(() =>
    props.products?.data ?? (Array.isArray(props.products) ? props.products : [])
);

const pagination = computed(() => ({
    meta: props.products?.meta ?? (props.products?.current_page ? props.products : null),
    links: props.products?.links ?? [],
}));

const sortedProducts = computed(() => {
    const dir = sortDirection.value === 'asc' ? 1 : -1;

    return [...productsList.value].sort((a, b) => {
        const va = a[sortKey.value];
        const vb = b[sortKey.value];
        return typeof va === 'number'
            ? (va - vb) * dir
            : String(va).localeCompare(String(vb), 'pt-BR', { sensitivity: 'base' }) * dir;
    });
});

// ─── Moeda ─────────────────────────────────────────────────────────────────

const brl = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

const formatPrice  = (v) => brl.format(Number(v ?? 0));
const formatInput = (v) => {
    const d = String(v ?? '').replace(/\D/g, '').slice(0, 12);
    return d ? brl.format(Number(d) / 100) : '';
};
const unformatInput = (v) => Number(String(v ?? '').replace(/\D/g, '')) / 100;

// ─── Modal / Formulário ────────────────────────────────────────────────────

const isModalOpen      = ref(false);
const editingProductId = ref(null);
const isSaving         = ref(false);

const form = useForm({
    name: '', description: '', status: 'ativo',
    quantity: 0, cost_price: '', sale_price: '', supplier: '',
});

const openModal = (product = null) => {
    editingProductId.value = product?.id ?? null;
    form.name        = product?.name        ?? '';
    form.description = product?.description ?? '';
    form.status      = product?.status      ?? 'ativo';
    form.quantity    = product?.quantity    ?? 0;
    form.cost_price  = product ? formatInput(String(product.cost_price ?? '')) : '';
    form.sale_price  = product ? formatInput(String(product.sale_price ?? '')) : '';
    form.supplier    = product?.supplier    ?? '';
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    editingProductId.value = null;
    form.reset();
    form.clearErrors();
};

const saveProduct = () => {
    if (isSaving.value) return;
    isSaving.value = true;
    
    const payload = {
        ...form.data(),
        cost_price: unformatInput(form.cost_price),
        sale_price: unformatInput(form.sale_price),
        supplier:   form.supplier || null,
    };

    const isEditing = !!editingProductId.value;
    const url    = isEditing ? route('products.update', editingProductId.value) : route('products.store');
    const method = isEditing ? 'put' : 'post';

    const successMessage = isEditing ? 'Produto atualizado' : 'Produto adicionado';

    router[method](url, payload, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal();
            Swal.fire({
                position: 'bottom',
                toast: true,
                title: successMessage,
                icon: 'success',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                background: '#ffffff',
                color: '#0b1526',
            });
        },
        onError: (errors) => {
        const messages = Object.values(errors).flat().join('\n');
            Swal.fire({
                title: 'Erro ao salvar',
                text: messages,
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#ff8a2a',
                background: '#0b1526',
                color: '#ffffff',
            });
        },
        onFinish: () => { isSaving.value = false; },
    });
};

const deleteProduct = (product) => {
    Swal.fire({
        title: 'Excluir produto?',
        text: `Tem certeza que deseja excluir "${product.name}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#334155',
        background: '#0b1526',
        color: '#ffffff',
    }).then(({ isConfirmed }) => {
        if (!isConfirmed) return;

        router.delete(route('products.destroy', product.id), {
            preserveScroll: true,
            onSuccess: () => {
                Swal.fire({
                    position: 'bottom',
                    toast: true,
                    title: 'Produto removido',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 4000,
                    timerProgressBar: true,
                    background: '#ffffff',
                    color: '#0b1526',
                });
            },
        });
    });
};

const generateProducts = () => {
    Swal.fire({
        title: 'Gerar 30 produtos?',
        text: 'Serão criados 30 produtos reais de empresas como Apple, Samsung, Sony, etc.',
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Gerar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#334155',
        background: '#0b1526',
        color: '#ffffff',
    }).then(({ isConfirmed }) => {
        if (!isConfirmed) return;

        router.post(route('products.generate'), {}, {
            preserveScroll: true,
            onSuccess: () => Swal.fire({
                title: 'Sucesso!',
                text: 'Produtos gerados com sucesso.',
                icon: 'success',
                confirmButtonText: 'OK',
                confirmButtonColor: '#ff8a2a',
                background: '#0b1526',
                color: '#ffffff',
            }),
        });
    });
};

// ─── Colunas da tabela ─────────────────────────────────────────────────────

const columns = [
    { key: 'name',        label: 'Nome'         },
    { key: 'description', label: 'Descrição'    },
    { key: 'quantity',    label: 'Quant.'        },
    { key: 'cost_price',  label: 'Preço custo'  },
    { key: 'sale_price',  label: 'Preço venda'  },
    { key: 'actions',     label: 'Ações'         },
];

// ─── Filtros de preço ───────────────────────────────────────────────────────

const costPriceOptions = computed(() =>
    props.cost_price_filter.map((v) => ({ value: String(v), label: brl.format(Number(v)) }))
);

const salePriceOptions = computed(() =>
    props.sale_price_filter.map((v) => ({ value: String(v), label: brl.format(Number(v)) }))
);

const toggleOption = (list, value) => {
    const idx = list.indexOf(value);
    if (idx !== -1) list.splice(idx, 1);
    else list.push(value);

    if (list === selectedCostPrices.value) {
        selectedSalePrices.value = [];
    } else if (list === selectedSalePrices.value) {
        selectedCostPrices.value = [];
    }
};

const costLabel = computed(() => {
    if (!selectedCostPrices.value.length) return 'Selecionar';
    return costPriceOptions.value
        .filter((o) => selectedCostPrices.value.includes(o.value))
        .map((o) => o.label)
        .join(', ');
});

const saleLabel = computed(() => {
    if (!selectedSalePrices.value.length) return 'Selecionar';
    return salePriceOptions.value
        .filter((o) => selectedSalePrices.value.includes(o.value))
        .map((o) => o.label)
        .join(', ');
});

const clearCostFilter = () => { selectedCostPrices.value = []; };
const clearSaleFilter = () => { selectedSalePrices.value = []; };
</script>

<template>
    <Head title="Produtos" />

    <AuthenticatedLayout>
        <div class="relative mx-auto flex min-h-[calc(100vh-9rem)] w-full max-w-[88rem] gap-6 overflow-x-hidden px-4 py-6 sm:px-6 lg:px-8">
            <DashboardSidebar />

            <div class="min-w-0 flex-1">
                <div class="flex min-h-[calc(100vh-9rem)] flex-col rounded-[2rem] border border-white/10 bg-white/5 p-6 shadow-[0_24px_60px_rgba(0,0,0,0.22)] backdrop-blur">
                    <div class="flex min-h-0 flex-1 flex-col gap-6">

                        <!-- Cabeçalho -->
                        <div class="space-y-2">
                            <p class="text-sm font-semibold uppercase tracking-[0.32em] text-[#8fb0ff]">Produtos</p>
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <h1 class="text-3xl font-semibold text-white sm:text-4xl">Listagem de produtos</h1>
                                    <p class="mt-1 text-sm text-white/60">Cadastre, edite e acompanhe produtos com custo, venda e fornecedor.</p>
                                </div>

                                <div class="flex shrink-0 gap-2">
                                    <button
                                        type="button"
                                        class="rounded-full bg-blue-300 px-4 py-2 text-sm font-semibold text-blue-900 transition hover:bg-blue-400"
                                        @click="generateProducts()"
                                    >
                                        Gerar 30 produtos
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-full bg-[#ff8a2a] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#ff7a14]"
                                        @click="openModal()"
                                    >
                                        Novo produto
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Busca + filtros de preço -->
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center">

                            <!-- Search -->
                            <div class="flex-1">
                                <p class="mb-1 text-xs font-semibold uppercase tracking-[0.16em] text-white/55">Busca</p>
                                <input
                                    v-model="search"
                                    type="search"
                                    placeholder="Digite nome, categoria, status ou responsável"
                                    class="w-full rounded-2xl border border-white/10 bg-[#0f1b31] px-4 py-3 text-sm text-white placeholder:text-white/35 shadow-sm outline-none transition focus:border-[#ff8a2a]/60 focus:ring-2 focus:ring-[#ff8a2a]/30"
                                />
                            </div>

                            <!-- Filtro preço custo -->
                            <div class="relative w-full lg:w-48">
                                <p class="mb-1 text-xs font-semibold uppercase tracking-[0.16em] text-white/55">Custo</p>
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between rounded-2xl border border-white/10 bg-[#0f1b31] px-3 py-2 text-sm text-white/80 transition hover:border-[#ff8a2a]/50"
                                    @click="showCostDropdown = !showCostDropdown; showSaleDropdown = false"
                                >
                                    <span class="truncate" :class="selectedCostPrices.length ? 'text-[#ff8a2a]' : 'text-white/45'">{{ costLabel }}</span>
                                    <svg class="ml-2 h-4 w-4 shrink-0 text-white/40" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.293l3.71-4.06a.75.75 0 111.12 1L10.56 13.4a.75.75 0 01-1.12 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                </button>
                                <div
                                    v-if="showCostDropdown"
                                    class="absolute left-0 z-50 mt-2 w-full overflow-hidden rounded-2xl border border-white/10 bg-[#0a1324] shadow-[0_16px_40px_rgba(0,0,0,0.45)]"
                                >
                                    <div class="max-h-52 overflow-auto p-2">
                                        <button
                                            v-for="option in costPriceOptions"
                                            :key="option.value"
                                            type="button"
                                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-sm transition hover:bg-white/8"
                                            :class="selectedCostPrices.includes(option.value) ? 'text-white' : 'text-white/65'"
                                            @click="toggleOption(selectedCostPrices, option.value)"
                                        >
                                            <span
                                                class="flex h-4 w-4 shrink-0 items-center justify-center rounded border"
                                                :class="selectedCostPrices.includes(option.value) ? 'border-[#ff8a2a] bg-[#ff8a2a]' : 'border-white/25'"
                                            >
                                                <svg v-if="selectedCostPrices.includes(option.value)" class="h-2.5 w-2.5 text-white" viewBox="0 0 12 12" fill="none"><polyline points="1.5,6 4.5,9 10.5,3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </span>
                                            {{ option.label }}
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between border-t border-white/10 px-4 py-2 text-xs">
                                        <span class="text-white/50">{{ selectedCostPrices.length }} marcados</span>
                                        <div class="flex gap-3">
                                            <button type="button" class="text-[#ff8a2a]" @click="clearCostFilter">Limpar</button>
                                            <button type="button" class="text-white/60" @click="showCostDropdown = false">Fechar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Filtro preço venda -->
                            <div class="relative w-full lg:w-48">
                                <p class="mb-1 text-xs font-semibold uppercase tracking-[0.16em] text-white/55">Venda</p>
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between rounded-2xl border border-white/10 bg-[#0f1b31] px-3 py-2 text-sm text-white/80 transition hover:border-[#ff8a2a]/50"
                                    @click="showSaleDropdown = !showSaleDropdown; showCostDropdown = false"
                                >
                                    <span class="truncate" :class="selectedSalePrices.length ? 'text-[#ff8a2a]' : 'text-white/45'">{{ saleLabel }}</span>
                                    <svg class="ml-2 h-4 w-4 shrink-0 text-white/40" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.293l3.71-4.06a.75.75 0 111.12 1L10.56 13.4a.75.75 0 01-1.12 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                                </button>
                                <div
                                    v-if="showSaleDropdown"
                                    class="absolute left-0 z-50 mt-2 w-full overflow-hidden rounded-2xl border border-white/10 bg-[#0a1324] shadow-[0_16px_40px_rgba(0,0,0,0.45)]"
                                >
                                    <div class="max-h-52 overflow-auto p-2">
                                        <button
                                            v-for="option in salePriceOptions"
                                            :key="option.value"
                                            type="button"
                                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-sm transition hover:bg-white/8"
                                            :class="selectedSalePrices.includes(option.value) ? 'text-white' : 'text-white/65'"
                                            @click="toggleOption(selectedSalePrices, option.value)"
                                        >
                                            <span
                                                class="flex h-4 w-4 shrink-0 items-center justify-center rounded border"
                                                :class="selectedSalePrices.includes(option.value) ? 'border-[#ff8a2a] bg-[#ff8a2a]' : 'border-white/25'"
                                            >
                                                <svg v-if="selectedSalePrices.includes(option.value)" class="h-2.5 w-2.5 text-white" viewBox="0 0 12 12" fill="none"><polyline points="1.5,6 4.5,9 10.5,3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </span>
                                            {{ option.label }}
                                        </button>
                                    </div>
                                    <div class="flex items-center justify-between border-t border-white/10 px-4 py-2 text-xs">
                                        <span class="text-white/50">{{ selectedSalePrices.length }} marcados</span>
                                        <div class="flex gap-3">
                                            <button type="button" class="text-[#ff8a2a]" @click="clearSaleFilter">Limpar</button>
                                            <button type="button" class="text-white/60" @click="showSaleDropdown = false">Fechar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Tabela -->
                        <div class="h-[calc(100vh-22rem)] min-h-[18rem] overflow-hidden rounded-[1.75rem] border border-white/10 bg-[#0b1526]/90 flex flex-col">

                            <div v-if="sortedProducts.length" class="flex-1 overflow-auto min-h-0">
                                <table class="min-w-[920px] w-full table-fixed border-collapse">
                                    <colgroup>
                                        <col style="width: 25%" />
                                        <col style="width: 30%" />
                                        <col style="width: 15%" />
                                        <col style="width: 15%" />
                                        <col style="width: 15%" />
                                        <col style="width: 22%" />
                                    </colgroup>

                                    <thead class="border-b border-white/10 text-xs font-semibold uppercase tracking-[0.2em] text-white/45">
                                        <tr>
                                            <th v-for="col in columns" :key="col.key" scope="col" class="px-5 py-4 text-left">
                                                <button
                                                    v-if="col.key !== 'actions'"
                                                    type="button"
                                                    class="inline-flex items-center gap-2 whitespace-nowrap transition hover:text-white"
                                                    @click="toggleSort(col.key)"
                                                >
                                                    {{ col.label }}
                                                    <span
                                                        class="w-3 text-[10px] text-[#8fb0ff]"
                                                        :class="sortKey === col.key ? 'opacity-100' : 'opacity-0'"
                                                    >
                                                        {{ sortDirection === 'asc' ? '▲' : '▼' }}
                                                    </span>
                                                </button>
                                                <span v-else>{{ col.label }}</span>
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-white/10 text-sm text-white/80">
                                        <tr v-for="product in sortedProducts" :key="product.id" class="align-top transition hover:bg-white/5">
                                            <td class="px-5 py-4 font-medium text-white break-words whitespace-normal">{{ product.name }}</td>
                                            <td class="px-5 py-4 leading-6 text-white/70 break-words whitespace-normal">{{ product.description }}</td>
                                            <td class="px-5 py-4">{{ product.quantity }}</td>
                                            <td class="px-5 py-4">{{ formatPrice(product.cost_price) }}</td>
                                            <td class="px-5 py-4">{{ formatPrice(product.sale_price) }}</td>
                                            <td class="px-5 py-4">
                                                <div class="flex flex-wrap gap-2">
                                                    <button
                                                        type="button"
                                                        class="rounded-full border border-white/60 bg-black/50 px-3 py-1.5 text-xs font-medium text-white/80 transition hover:border-[#8fb0ff]/50 hover:bg-[#8fb0ff]/10"
                                                        @click="openModal(product)"
                                                    >
                                                        Abrir
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="rounded-full border border-white/10 bg-red-400/80 px-3 py-1.5 text-xs font-medium text-white/80 transition hover:border-red-400/50 hover:bg-red-400/10"
                                                        @click="deleteProduct(product)"
                                                    >
                                                        Excluir
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div v-else class="flex h-full items-center px-5 py-10 text-sm text-white/55">
                                Nenhum produto encontrado para essa busca.
                            </div>

                            <!-- Paginação -->
                            <div v-if="pagination.meta && pagination.links.length" class="border-t border-white/10 bg-[#071224] px-4 py-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-white/70">
                                        Página {{ pagination.meta.current_page }} de {{ pagination.meta.last_page }}
                                    </span>
                                    <nav class="flex items-center gap-2" aria-label="Pagination">
                                        <button
                                            v-for="link in pagination.links"
                                            :key="link.label + String(link.url)"
                                            type="button"
                                            class="px-3 py-1 text-sm rounded"
                                            :class="link.active
                                                ? 'bg-[#ff8a2a] text-white'
                                                : link.url ? 'bg-white/5 text-white' : 'opacity-40 cursor-not-allowed'"
                                            v-html="link.label"
                                            @click.prevent="link.url && router.get(link.url)"
                                        />
                                    </nav>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Modal de criação / edição -->
            <div
                v-if="isModalOpen"
                class="fixed inset-0 z-[120] flex items-center justify-center bg-black/60 px-4 py-6 backdrop-blur-sm"
            >
                <div class="w-full max-w-2xl rounded-[1.5rem] border border-white/10 bg-[#0b1526] p-6 shadow-[0_24px_60px_rgba(0,0,0,0.45)]">

                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#8fb0ff]">Produtos</p>
                            <h2 class="mt-1 text-2xl font-semibold text-white">
                                {{ editingProductId ? 'Editar produto' : 'Novo produto' }}
                            </h2>
                        </div>
                        <button type="button" class="text-white/55 transition hover:text-white" @click="closeModal">Fechar</button>
                    </div>

                    <form class="mt-6 grid gap-4" @submit.prevent="saveProduct">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-white/70">Nome <span class="text-[#ff8a2a]">*</span></span>
                                <input v-model="form.name" type="text" required class="rounded-2xl border border-white/10 bg-[#0f1b31] px-4 py-3 text-white outline-none focus:border-[#ff8a2a]/60" />
                            </label>

                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-white/70">Status <span class="text-[#ff8a2a]">*</span></span>
                                <select v-model="form.status" required class="rounded-2xl border border-white/10 bg-[#0f1b31] px-4 py-3 text-white outline-none focus:border-[#ff8a2a]/60">
                                    <option value="ativo">Ativo</option>
                                    <option value="pausado">Pausado</option>
                                </select>
                            </label>
                        </div>

                        <label class="grid gap-2">
                            <span class="text-sm font-medium text-white/70">Descrição</span>
                            <textarea v-model="form.description" rows="4" class="rounded-2xl border border-white/10 bg-[#0f1b31] px-4 py-3 text-white outline-none focus:border-[#ff8a2a]/60" />
                        </label>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-white/70">Quantidade <span class="text-[#ff8a2a]">*</span></span>
                                <input v-model.number="form.quantity" type="number" min="0" required class="rounded-2xl border border-white/10 bg-[#0f1b31] px-4 py-3 text-white outline-none focus:border-[#ff8a2a]/60" />
                            </label>

                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-white/70">Fornecedor</span>
                                <input v-model="form.supplier" type="text" placeholder="Opcional" class="rounded-2xl border border-white/10 bg-[#0f1b31] px-4 py-3 text-white outline-none focus:border-[#ff8a2a]/60" />
                            </label>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-white/70">Custo <span class="text-[#ff8a2a]">*</span></span>
                                <input
                                    :value="form.cost_price"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="R$ 0,00"
                                    required
                                    class="rounded-2xl border border-white/10 bg-[#0f1b31] px-4 py-3 text-white outline-none focus:border-[#ff8a2a]/60"
                                    @input="form.cost_price = formatInput($event.target.value); $event.target.value = form.cost_price"
                                />
                            </label>

                            <label class="grid gap-2">
                                <span class="text-sm font-medium text-white/70">Preço de venda <span class="text-[#ff8a2a]">*</span></span>
                                <input
                                    :value="form.sale_price"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="R$ 0,00"
                                    required
                                    class="rounded-2xl border border-white/10 bg-[#0f1b31] px-4 py-3 text-white outline-none focus:border-[#ff8a2a]/60"
                                    @input="form.sale_price = formatInput($event.target.value); $event.target.value = form.sale_price"
                                />
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" class="rounded-full border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-white/80" @click="closeModal">
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="isSaving"
                                class="rounded-full bg-[#ff8a2a] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#ff7a14] disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                {{ isSaving ? 'Salvando...' : 'Salvar' }}
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>