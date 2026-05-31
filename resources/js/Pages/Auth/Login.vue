<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const productHighlights = [
    {
        title: 'Gestão comercial',
        description: 'Pipeline, acompanhamento e previsões em uma visão clara para o time vender com mais ritmo.',
    },
    {
        title: 'Operação conectada',
        description: 'Pedidos, status e rotina centralizados para reduzir ruído entre áreas e acelerar decisões.',
    },
    {
        title: 'Indicadores acionáveis',
        description: 'Painéis objetivos para enxergar margem, performance e oportunidades sem perder contexto.',
    },
];

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Entrar" />

        <div class="relative overflow-hidden rounded-[1.5rem] bg-[#f7f5f0] shadow-[0_24px_60px_rgba(15,23,42,0.12)] ring-1 ring-black/5">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(255,125,0,0.16),_transparent_34%),radial-gradient(circle_at_left,_rgba(25,118,210,0.12),_transparent_28%)]"></div>

            <div class="relative grid lg:grid-cols-[1fr_0.9fr]">
                <section class="hidden flex-col justify-between bg-[#0f172a] px-8 py-10 text-white lg:flex lg:px-10 lg:py-10">
                    <div class="space-y-6">
                        <div class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/5 px-3 py-2 text-xs tracking-[0.2em] text-white/80 uppercase">
                            FirstDecision
                        </div>

                        <div class="max-w-lg space-y-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#ff8a2a]">
                                Plataforma de produtos e operação
                            </p>
                            <h1 class="text-3xl font-semibold leading-tight text-balance xl:text-4xl">
                                Acesse o ecossistema da FirstDecision com visão clara do que move o negócio.
                            </h1>
                            <p class="max-w-lg text-sm leading-6 text-white/72 xl:text-base">
                                Entre para acompanhar produtos, decisões e indicadores em um ambiente pensado para times que precisam agir rápido.
                            </p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
                            <article
                                v-for="item in productHighlights"
                                :key="item.title"
                                class="rounded-2xl border border-white/10 bg-white/6 p-3 backdrop-blur"
                            >
                                <div class="mb-3 h-1.5 w-12 rounded-full bg-[#ff8a2a]"></div>
                                <h2 class="text-sm font-semibold text-white">
                                    {{ item.title }}
                                </h2>
                                <p class="mt-1 text-sm leading-5 text-white/68">
                                    {{ item.description }}
                                </p>
                            </article>
                        </div>
                    </div>

                    <div class="mt-8 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-3">
                            <p class="text-xl font-semibold text-white">24/7</p>
                            <p class="mt-1 text-sm text-white/65">Acesso contínuo à operação</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-3">
                            <p class="text-xl font-semibold text-white">+3</p>
                            <p class="mt-1 text-sm text-white/65">Áreas conectadas em um só fluxo</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-3">
                            <p class="text-xl font-semibold text-white">1 visão</p>
                            <p class="mt-1 text-sm text-white/65">Para priorizar produtos e decisões</p>
                        </div>
                    </div>
                </section>

                <section class="flex items-center justify-center px-4 py-6 sm:px-6 lg:px-8">
                    <div class="w-full max-w-sm rounded-[1.5rem] border border-white/70 bg-white/90 p-6 shadow-[0_18px_45px_rgba(15,23,42,0.08)] backdrop-blur sm:max-w-md sm:p-7">
                        <div class="mb-6 space-y-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[#ff7a1a]">
                                Entrar
                            </p>
                            <h2 class="text-2xl font-semibold text-slate-900 sm:text-3xl">
                                Bem-vindo de volta
                            </h2>
                            <p class="text-sm leading-6 text-slate-600">
                                Entre para continuar sua jornada com os produtos da FirstDecision.
                            </p>
                        </div>

                        <div v-if="status" class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {{ status }}
                        </div>

                        <form class="space-y-4" @submit.prevent="submit">
                            <div>
                                <InputLabel for="email" value="E-mail" class="text-slate-700" />

                                <TextInput
                                    id="email"
                                    type="email"
                                    class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-2.5 shadow-sm focus:border-[#ff7a1a] focus:ring-[#ff7a1a]"
                                    v-model="form.email"
                                    required
                                    autofocus
                                    autocomplete="username"
                                />

                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div>
                                <InputLabel for="password" value="Senha" class="text-slate-700" />

                                <TextInput
                                    id="password"
                                    type="password"
                                    class="mt-2 block w-full rounded-2xl border-slate-200 bg-white px-4 py-2.5 shadow-sm focus:border-[#ff7a1a] focus:ring-[#ff7a1a]"
                                    v-model="form.password"
                                    required
                                    autocomplete="current-password"
                                />

                                <InputError class="mt-2" :message="form.errors.password" />
                            </div>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <label class="flex items-center">
                                    <Checkbox name="remember" v-model:checked="form.remember" />
                                    <span class="ms-2 text-sm text-slate-600">Lembrar de mim</span>
                                </label>

                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="text-sm font-medium text-[#cf5d00] transition hover:text-[#9e4800]"
                                >
                                    Esqueceu sua senha?
                                </Link>
                            </div>

                            <PrimaryButton
                                class="flex w-full justify-center rounded-2xl bg-[#0f172a] px-4 py-2.5 text-sm font-semibold uppercase tracking-[0.18em] text-white transition hover:bg-[#17233b]"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                Entrar
                            </PrimaryButton>

                            <p class="text-center text-sm text-slate-600">
                                Ainda não tem acesso?
                                <Link
                                    :href="route('register')"
                                    class="font-semibold text-[#cf5d00] transition hover:text-[#9e4800]"
                                >
                                    Criar conta
                                </Link>
                            </p>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </GuestLayout>
</template>
